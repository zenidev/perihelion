<?php

class DesignerViewController {

	private $urlArray;
	private $inputArray;
	private $errorArray;
	
	public function __construct($urlArray, $inputArray, $errorArray) {

		$this->urlArray = $urlArray;
		$this->inputArray = $inputArray;
		$this->errorArray = $errorArray;
		
		$authorizedRoles = array('siteAdmin','siteManager','siteDesigner');
		$role = Auth::getUserRole();
		if (!in_array($role,$authorizedRoles)) { die("[DesignerViewController] You do not have view permissions for the designer module."); }
		
	}
	
	public function getView() {
		
		$menu = new MenuView($this->urlArray,$this->inputArray,$this->errorArray);
		$nav = $menu->designerSubMenu();

		if ($this->urlArray[1] == 'content') {

			$view = new ContentView($this->urlArray, $this->inputArray, $this->errorArray);
			if ($this->urlArray[1] == 'content' && $this->urlArray[2] == 'create') { return $nav . $view->contentForm('create'); }
			if ($this->urlArray[1] == 'content' && $this->urlArray[2] == 'update' && ctype_digit($this->urlArray[3])) {
				
				if ($this->urlArray[4] == 'images') { return $nav . $view->contentImagesForm($this->urlArray[3]); }
				return $nav . $view->contentForm('update',$this->urlArray[3]);
				
			}
			if ($this->urlArray[1] == 'content') { return $nav . $view->contentList(); }

		}
	
		if ($this->urlArray[1] == 'themes') {

			$view = new ThemeView($this->urlArray, $this->inputArray, $this->errorArray);
			if ($this->urlArray[1] == 'themes' && $this->urlArray[2] == 'create') { return $nav . $view->themeForm('create'); }
			if ($this->urlArray[1] == 'themes' && $this->urlArray[2] == 'update' && ctype_digit($this->urlArray[3])) { return $nav . $view->themeForm('update',$this->urlArray[3]); }
			if ($this->urlArray[1] == 'themes') { return $nav . $view->themeList(); }
			
		}
		
		if ($this->urlArray[1] == 'images') {
			
			$view = new ImageView($this->urlArray,$this->inputArray,$this->errorArray);
			if (ctype_digit($this->urlArray[2])) {
				return $nav . $view->imageManager('Site',$_SESSION['siteID'],$this->urlArray[2]);
			} else {
				return $nav . $view->imageManager('Site',$_SESSION['siteID']);
			}
			
		}
		
		if ($this->urlArray[1] == 'carousels') {
			
			$view = new CarouselView($this->urlArray,$this->inputArray,$this->errorArray);
			if ($this->urlArray[2] == 'create') { return $nav . $view->carouselForm('create'); }
			if ($this->urlArray[2] == 'update' && ctype_digit($this->urlArray[3])) { return $nav . $view->carouselForm('update',$this->urlArray[3]); }
			return $nav . $view->carouselList();
		}
		
		if ($this->urlArray[1] == 'menus') {
			
			if ($this->urlArray[2] == 'item' && $this->urlArray[3] == 'create') {
				$miv = new MenuItemView(0,$this->inputArray,$this->errorArray);
				return $nav . $miv->menuItemForm();
			} elseif ($this->urlArray[2] == 'item' && $this->urlArray[3] == 'update' && ctype_digit($this->urlArray[4])) {
				$miv = new MenuItemView($this->urlArray[4],$this->inputArray,$this->errorArray);
				return $nav . $miv->menuItemForm();
			} else {
				return $nav . $menu->menuManager();
			}
			
		}

		if ($this->urlArray[1] == 'seo') {
			
			$view = new SeoView($this->urlArray,$this->inputArray,$this->errorArray);
			if ($this->urlArray[2] == 'create') {
				return $nav . $view->seoForm();
			} elseif ($this->urlArray[2] == 'update' && ctype_digit($this->urlArray[3])) {
				return $nav . $view->seoForm($this->urlArray[3]);
			} else {
				return $nav . $view->seoList();
			}

		}

		if ($this->urlArray[1] == 'files') {
			
			$view = new FileView($this->urlArray,$this->inputArray,$this->errorArray);
			return $nav . $view->fileManager('Site',$_SESSION['siteID']);
			
		}
		
		if ($this->urlArray[1] == 'scripts') {

			$view = new ScriptView($this->urlArray, $this->inputArray, $this->errorArray);
			if ($this->urlArray[1] == 'scripts' && $this->urlArray[2] == 'create') { return $nav . $view->scriptForm('create'); }
			if ($this->urlArray[1] == 'scripts' && $this->urlArray[2] == 'update' && ctype_digit($this->urlArray[3])) { return $nav . $view->scriptForm('update',$this->urlArray[3]); }
			if ($this->urlArray[1] == 'scripts') { return $nav . $view->scriptList(); }
			
		}
		
		if ($this->urlArray[1] == 'tiles') {
			$view = new TileView($this->urlArray,$this->inputArray,$this->errorArray);
			return $nav . $view->tileList();
		}

	}
	
}

?>