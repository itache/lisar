<?php
namespace app\models;

use app\exceptions\GalleryException;
use app\exceptions\ImageNotFoundException;


class GalleryLoader
{
    private $itemList;
    private $itemHandler;
    private $loaded = [];
    private $failed = [];

    public function __construct()
    {
        $this->itemList = new Gallery();
        $this->itemHandler = new GoogleDriveGalleryItemCreator();
    }

    public function setItemList(Gallery $itemList)
    {
        $this->itemList = $itemList;
    }

    public function setItemHandler(GalleryItemCreator $itemHandler)
    {
        $this->itemHandler = $itemHandler;
        
    }

    public function load($path)
    {
        try {
            if (!file_exists($path)) throw new ImageNotFoundException("Image $path doesn't exist");
            $item = $this->itemHandler->create($path);
			error_log("Item created: ".implode(",",$item->toArray()));
            $this->itemList->add($item);
            $this->loaded[] =$path;
        }catch (\Exception $e){
            $failed['path'] = $path;
            $failed['cause'] = $e->getMessage();
            $this->failed[] = $failed;
        }
    }

    public function getLoaded(){
        return $this->loaded;
    }

    public function getFailed(){
        return $this->failed;
    }

    public function hasErrors(){
        return !empty($this->failed);
    }

    public function loadFromDir($dir)
    {
        $paths = $this->getImagePaths($dir);
		error_log("Image paths: ".implode(',', $paths));
        foreach ($paths as $path) {
            $this->load($path);
        }
    }

    public static function build(array $cfg){
        $loader = new GalleryLoader();
        $itemList = new Gallery();
        $itemList->fromJson($cfg['json']);
        $loader->setItemList($itemList);
        $itemHandler = new GoogleDriveGalleryItemCreator();
        $itemHandler->setFullImageProps($cfg['full']);
        $itemHandler->setLowImageProps($cfg['low']);
        $loader->setItemHandler($itemHandler);
        return $loader;
    }

    private function getImagePaths($dir)
    {
        $paths = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $filename) {
            if ($filename->isDir()) continue;
			error_log("File found: ".$filename);
            if (!in_array(strtolower($filename->getExtension()), ['jpg', 'jpeg', 'png'])) continue;
            $paths[] = $filename->getPathname();
        }
        return $paths;
    }
}
