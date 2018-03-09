<?php

namespace app\controllers;

use app\models\Map;
use app\models\Cities;
use app\models\Property;
use app\models\PropertyDetails;
use app\models\Countries;

class MapController extends \yii\web\Controller
{
    /*
     *  Get all map data
     * 
     */
    
    public function actionIndex()
    {
        $propertyMap = Map::getCountryCoord();
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $propertyMap;
		
    }
	
	
	public function actionView()
    {
        $model = new Cities();
        $propertyMap = $model->find()->one();
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $propertyMap;
    }
	
    
    /*
    *
    *  random generate rows in property table
    *
    */
	
	public function actionFillmap(){
		
		$request = \Yii::$app->request->get('url');
		$saved = 0;
		$failed = 0;
		
		for($i = 0; $i<$request; $i++){
			$model = new Property();
			$model->insertRandom();
			$result = $model->save();
			$model = null;
			if ($result) $saved++; else $failed++;
		}
		
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return array("Saved"=>$saved,"Failed"=>$failed);
		
	}
    
	/*
	 * Get data for map
	 */
	public function actionGet()
    {
		$data = \Yii::$app->request->get();
		
		$type = $data['type'];
		$method = $data['method'];
		$action = $data['action'];
		
		$propertyMap = [];
		
		switch($type)
		{
			case 'property':
			
				switch($method)
				{
					case 'full':
						switch($action){
							case 'count':
								$propertyMap = Map::getCountProperty();
							break;
							case 'info':
								$propertyMap = Map::getPropertyAll();
							break;
							
						}
					break;
					
					case 'boundary':
						switch($action){
							case 'count':
								$propertyMap = explode(",", $data['bounds']);
								$propertyMap = Map::getCountBoundary($propertyMap);
							break;
							case 'info':
								$propertyMap = explode(",", $data['bounds']);
								$propertyMap = Map::getPropertyBoundary($propertyMap);
							break;
							
						}
					break;
					
					case 'country':
						switch($action){
							case 'count':
								$propertyMap = Map::getCountryCount($data['country']);
							break;
							case 'info':
								$propertyMap = Map::getPropertyCountry($data['country']);
							break;
						}
					break;
				}
			break;
                
            case 'country':
			
				switch($method)
				{
					case 'full':
						switch($action){
							case 'count':
								$propertyMap = Map::getCountProperty();
							break;
							case 'info':
								$propertyMap = Map::getPropertyAll();
							break;
							
						}
					break;
					
					case 'boundary':
						switch($action){
							case 'count':
								$propertyMap = explode(",", $data['bounds']);
								$propertyMap = Map::getCountBoundary($propertyMap);
							break;
							case 'info':
								$propertyMap = explode(",", $data['bounds']);
								$propertyMap = Map::getBoundaryCountry($propertyMap);
							break;
							
						}
					break;
					
				}
			break;
                
            case 'city':
			
				switch($method)
				{
					case 'full':
						switch($action){
							case 'count':
								$propertyMap = Map::getCountProperty();
							break;
							case 'info':
								$propertyMap = Map::getPropertyAll();
							break;
							
						}
					break;
					
					case 'boundary':
						switch($action){
							case 'count':
								$propertyMap = explode(",", $data['bounds']);
								$propertyMap = Map::getCountBoundary($propertyMap);
							break;
							case 'info':
								$propertyMap = explode(",", $data['bounds']);
								$propertyMap = Map::getCitiesBoundary($propertyMap);
							break;
							
						}
					break;
					
				}
			break;
			
		}
		
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $propertyMap;
		
	}
    
}
