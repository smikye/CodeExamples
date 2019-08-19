<?php

namespace User\Resource\Manager;

use User\Resource\Manager\EntityResourceManager;
use User\Service\ImageService;
use User\Collection\UserImageCollection;
use User\Doctrine\ORM\Entity\User;
use User\Resource\UserImage;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use DirectoryIterator;

class UserImageResourceManager extends EntityResourceManager
{
    /**
     * @param $id
     * @param $parameters
     * @param $entityClass
     * @param null $fetchMethod
     * @return mixed
     */
    public function fetch($id, $parameters, $entityClass, $fetchMethod = null)
    {
        $objectManager = $this->getObjectManager();
        $userId = (int)$this->getFromRouteMatch('user_id');
        $imageId = $this->getFromRouteMatch('image_id');

        /** @var User $user */
        $user = $objectManager->find(User::class, $userId);

        if (!$user) {
            $apiProblem = new ApiProblem(404, 'Not found.');
            return new ApiProblemResponse($apiProblem);
        }

        $image = ImageService::getFile(ImageService::USER_IMAGE_TYPE, $userId, $imageId);
        $userImage = new UserImage($user, $image);

        return $userImage;
    }

    /**
     * @param $parameters
     * @param $collectionClassName
     * @param $entityClass
     * @param null $fetchMethod
     * @return mixed
     */
    public function getCollection($parameters, $collectionClassName, $entityClass, $fetchMethod = null)
    {
        $objectManager = $this->getObjectManager();
        $userId = (int)$this->getFromRouteMatch('user_id');

        /** @var User $user */
        $user = $objectManager->find(User::class, $userId);

        if (!$user) {
            $apiProblem = new ApiProblem(404, 'Not found.');
            return new ApiProblemResponse($apiProblem);
        }

        $folderPath = ImageService::getImageFolderPath(
            ImageService::USER_IMAGE_TYPE,
            $user->getId(),
            true
        );

        $userImages = [];
        foreach (new DirectoryIterator($folderPath) as $element) {
            if ($element->isDot()) {
                continue;
            }

            if ($element->isFile()) {
                $image = clone $element;
                $userImage = new UserImage($user, $image);
                $userImages[] = $userImage;
            }
        }

        $userImageCollection = new UserImageCollection($user, $userImages);

        return $userImageCollection;
    }
}
