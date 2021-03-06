<?php
namespace app\controllers;

use app\models\GalleryLoader;
use app\models\Gallery;
use F3;

defined('ROOT') or die("Root directory didn't define");

class GalleryController{
	public function loadAll(){
		$config = require(ROOT.'/config/main.php');
		$loader = GalleryLoader::build($config['images']);
		error_log("Trying load images from dir ".ROOT.$config['uploadDir']);
		$loader->loadFromDir(ROOT.$config['uploadDir']);
        $this->clearUploadDir(ROOT.$config['uploadDir']);
        if(!$loader->hasErrors()){
            F3::reroute('list');
        }else{
            $view = new \View;
            echo $view->render('failed.htm','text/html',['items' => $loader->getFailed()]);
        }
	}

	public function viewAll(){
		$config = require(ROOT.'/config/main.php');
        $gallery = new Gallery();
        $gallery->fromJson($config['images']['json']);
		$view = new \View;
		echo $view->render('list.htm','text/html',['items' => $gallery->toArray()]);
	}

	public function delete($name){
		$config = require(ROOT.'/config/main.php');
		$gallery = new Gallery();
		$gallery->fromJson($config['images']['json']);
		$gallery->deleteByName($name);
		F3::reroute('/admin/list');
	}

	public function deleteAll(){
		$config = require(ROOT.'/config/main.php');
		$gallery = new Gallery();
		$gallery->fromJson($config['images']['json']);
        $gallery->deleteAll();
		F3::reroute('upload');
	}

	private function clearUploadDir($dir)
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
			if($file->isFile() && $file->getFilename() != "mock.txt") {
				unlink($file->getPathname()); // delete file
				rmdir($file->getPath());
			}
		}
    }
}
