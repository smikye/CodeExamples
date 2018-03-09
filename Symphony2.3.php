<?php

namespace BKontor\AdminBundle\ChartUpdater;

use BKontor\BaseBundle\Entity\ChartCategory;
use BKontor\BaseBundle\Entity\ChartEntry;
use BKontor\BaseBundle\Entity\Currency;
use BKontor\BaseBundle\Entity\Market;
use BKontor\BaseBundle\Repository\ChartEntryRepository;
use BKontor\BaseBundle\Service\EventService;

class BraveNewCoinUpdater extends AbstractMarketBasedChartsUpdater
{
    const URL_TEMPLATE = 'https://bravenewcoin-v1.p.mashape.com/convert?from=%s&qty=1&to=usd';
    const CHART_TEMPLATE = 'BNC_%s_USD';

    private $lastModifieds = array();
    private $curl;

    /**
     * Get the interval, in seconds, in which this Updater should be called.
     * This may change dynamically. It will be called again,
     * after every call to runUpdate().
     *
     * @return integer  the number of seconds to wait before the next execution
     */
    public function getUpdateInterval()
    {
        return $this->getSetting('updateInterval') && is_numeric($this->getSetting('updateInterval')) ? $this->getSetting('updateInterval') : 300;
    }

    /**
     * Run the update process of this Updater.
     */
    public function runUpdate()
    {
        $this->checkMarketUpdate();
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HEADER, true);

        /** @var $currencies \BKontor\BaseBundle\Entity\Currency[] */
        $currencies = $this->container->get('bkontor.license')->getLicensedCurrencies();
        /** @var $chartRepo ChartEntryRepository */
        $chartRepo = $this->em->getRepository('BKontorBaseBundle:ChartEntry');
        foreach ($currencies as $currency) {
            $curId = $currency->getId();
            /*
             * Skip non-crypto
             * Skip where manual conversion is set
             */
            if ($currency->isType(Currency::TYPE_FIAT) || $currency->isType(Currency::TYPE_STOCK) || $currency->isType(Currency::TYPE_ASSET) || $currency->isManualConversionInput()) {
                continue;
            }

            // Find ChartCategory
            $chartName = sprintf(self::CHART_TEMPLATE, $curId);
            $chartCategory = $this->em->getRepository('BKontorBaseBundle:ChartCategory')->findOneBy(array('name' => $chartName));
            if (!$chartCategory instanceof ChartCategory) {
                $chartCategory = new ChartCategory;
                $chartCategory->setName($chartName)->setColor('');
                $this->em->persist($chartCategory);
                $this->em->flush($chartCategory);
            }

            $url = sprintf(self::URL_TEMPLATE, strtolower($curId));
            curl_setopt($this->curl, CURLOPT_URL, $url);
            $headers = [
                'X-Mashape-Key: ' . self::MASHAPE_KEY,
                'Accept: application/json'
            ];
            if (array_key_exists($curId, $this->lastModifieds)) {
                $headers[] = 'If-Modified-Since: ' . $this->lastModifieds[$curId];
            }
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($this->curl);
            $responseInfo = curl_getinfo($this->curl);

            if ($responseInfo['http_code'] != 200) {
                continue;
            }

            $lastMod = $this->getLastModified($response);
            if ($lastMod) {
                $this->lastModifieds[$curId] = $lastMod;
            }

            $data = json_decode($this->getResponseContent($response), true);
            if (is_array($data)
                && array_key_exists('request_date', $data)
                && array_key_exists('to_quantity', $data)
            ) {
                // Skip invalid timestamps
                $dt = new \DateTime($data['request_date'], new \DateTimeZone('UTC'));
                if (!$dt instanceof \DateTime) {
                    continue;
                }

                // Skip duplicates
                $chartEntry = $this->em
                    ->createQuery('SELECT ce FROM BKontorBaseBundle:ChartEntry ce WHERE ce.category = :category AND ce.created_at = :created_at')
                    ->setMaxResults(1)
                    ->setParameters(array(
                        'category' => $chartCategory,
                        'created_at' => $dt
                    ))
                    ->getOneOrNullResult()
                ;
                if ($chartEntry) {
                    continue;
                }

                // Skip new entries without change
                $rate = $this->valConvServ->toInternal($data['to_quantity']);
                $lastEntry = $chartRepo->getLatestEntry($chartCategory);
                if ($lastEntry && $lastEntry->getPrice() == $rate) {
                    continue;
                }

                $chartEntry = new ChartEntry;
                $chartEntry->setCategory($chartCategory)->setCreatedAt($dt)->setPrice($rate);
                $this->em->persist($chartEntry);
                $this->em->flush($chartEntry);

                /** @var $eventServ EventService */
                $eventServ = $this->container->get('draglet.event');
                $eventServ->rateChanged($chartEntry);
            }
        }

        curl_close($this->curl);
    }


    /**
     * Update known Markets.
     */
    protected function updateMarkets()
    {
        $licensedMarkets = array();
        /** @var $activeMarkets Market[] */
        $activeMarkets = $this->em
            ->createQuery('SELECT m FROM BKontorBaseBundle:Market m JOIN m.nominal_currency nomCur WHERE m.active = 1 AND nomCur.type = :crypto')
            ->setParameter('crypto', Currency::TYPE_CRYPTO)
            ->getResult()
        ;
        foreach ($activeMarkets as $market) {
            if ($this->licenseService->hasCurrency($market->getNominalCurrency()->getId() &&
                $this->licenseService->hasCurrency($market->getLimitCurrency()->getId()))
            ) {
                $licensedMarkets[] = $market;
            }
        }
        $this->markets = $licensedMarkets;
    }

    private function getLastModified($response)
    {
        foreach (preg_split("#\r?\n#", $response) as $line) {
            $line = trim($line);
            if (preg_match('#^Last\-Modified#i', $line)) {
                $parts = explode(': ', $line);

                return $parts[1];
            }
        }

        return false;
    }

    private function getResponseContent($response)
    {
        return trim(implode("\n", array_slice(preg_split("\r?\n\r?\n", $response), 1)));
    }

}
