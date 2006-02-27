<?php

require_once(ROOT_DIR.'/core/model/model.php');
require_once(ROOT_DIR.'/core/view/view.php');

class SActionController
{   
    public $request  = null;
    public $session  = null;
    public $response = null;
    public $flash    = null;
    
    public $layout      = false;
    public $useModels   = array();
    public $useHelpers  = array();
    
    public $cachePages     = array();
    public $cacheActions   = array();
    public $pageCacheDir   = null;
    public $pageCacheExt   = '.html';
    public $performCaching = True;
    
    public $beforeFilters = array();
    public $afterFilters  = array();
    public $autoCompleteFor = array();
    
    protected $virtualMethods    = array();
    protected $performedRender   = false;
    protected $performedRedirect = false;
    
    public function __construct()
    {
        $this->request  = SContext::$request;
        $this->session  = SContext::$session;
        $this->response = SContext::$response;
        
        $this->flash = new SFlash();
        
        $this->params = $this->request->params;
        
        $this->pageCacheDir = ROOT_DIR.'/public/cache';
        
        foreach($this->autoCompleteFor as $params)
        {
            $method = 'autoCompleteFor'.ucfirst($params[0]).ucfirst($params[1]);
            $this->virtualMethods[$method] = array('autoCompleteFor', $params);
        }
        
        foreach($this->useModels as $model) $this->requireModel($model);
        foreach($this->useHelpers as $helper) $this->requireHelper($helper);
    }
    
    public function __get($name)
    {
        if (isset($this->response[$name])) return $this->response[$name];
    }
    
    public function __set($name, $value)
    {
        $this->response[$name] = $value;
    }
    
    public function __call($action, $args)
    {
        if (in_array($action, array_keys($this->virtualMethods)))
        {
            $method = $this->virtualMethods[$action][0];
            $params = $this->virtualMethods[$action][1];
            $this->$method($params);
        }
    }
    
    public function actionExists($action)
    {
        try
        {
            $method = new ReflectionMethod(get_class($this), $action);
            if ($method->isPublic() && !$method->isConstructor()
                && $method->getDeclaringClass()->getName() != __CLASS__)
                return true;
            else
                return false;
        }
        catch (ReflectionException $e)
        {
            if (in_array($action, array_keys($this->virtualMethods)))
                return true;
            else
                return false;
        }
    }
    
    public function performAction($action)
    {
        foreach($this->beforeFilters as $method) $this->$method();
        $this->$action();
        foreach($this->afterFilters as $method) $this->$method();
        if (!$this->isPerformed()) $this->render();
        exit();
    }
    
    public function render()
    {
        $path = $this->defaultTemplatePath();
        if (!file_exists($path)) throw new SException('Template not found for this action');
        $this->renderFile($path);
    }
    
    public function renderText($str)
    {
        $this->performedRender = true;
        header("Content-Type: text/html; charset=utf-8");
        echo $str;
    }
    
    public function renderFile($path)
    {
        if (!$this->flash->isEmpty()) $this->response['flash'] = $this->flash->dump();
        $this->flash->discard();
        
        $renderer = new SRenderer($path, $this->response->values);
        if ($this->layout)
        {
            $layout = APP_DIR.'/layouts/'.$this->layout.'.php';
            if (!file_exists($layout)) throw new SException('Layout not found');
            $this->response['layout_content'] = $renderer->render();
            $renderer = new SRenderer($layout, $this->response->values);
        }
        $this->renderText($renderer->render());
    }
    
    protected function renderAction($action)
    {
        $this->renderFile($this->templatePath($this->request->module,
                                              $this->request->controller,
                                              $action));
    }
    
    protected function redirect($urlOptions)
    {
        $this->performedRedirect = true;
        if (!is_array($urlOptions))
        {
            $options = array();
            $options['action'] = $urlOptions;
        }
        else $options = $urlOptions;
        
        $this->redirectToPath($this->urlFor($options));
    }
    
    protected function redirectToPath($path)
    {
        header('location:'.$path);
        exit();
    }
    
    protected function urlFor($options)
    {
        if (!isset($options['action']))     $options['action'] = 'index';
        if (!isset($options['controller'])) $options['controller'] = $this->name();
        if (!isset($options['module']))     $options['module'] = $this->request->module;
        
        return SRoutes::rewriteUrl($options);
    }
    
    protected function sendFile($path, $params=array())
    {
        $fp = @fopen($path, "rb");
        if ($fp)
        {
            if (isset($params['type'])) 
                header("Content-Type: ".$params['type']);
            if (isset($params['disposition'])) 
                header("Content-disposition: ".$params['disposition']);
            fpassthru($fp);
            exit();
        }
        else
        {
            throw new SException('File not found : '.$path);
        }
    }
    
    protected function name()
    {
        return str_replace('controller', '', strtolower(get_class($this)));
    }
    
    protected function paginate($className, $perPage=10, $options=array())
    {
        $paginator = new SPaginator($className, $perPage, $options);
        return array($paginator, $paginator->currentPage());
    }
    
    /*protected function autoCompleteFor($args)
    {
        $object = $args[0];
        $method = $args[1];
        if (!isset($args[2])) $options = array();
        else $options = $args[2];
        
        $condition = "LOWER({$method}) LIKE '%".strtolower($this->params[$object][$method])."%'";
        $options = array_merge(array('order' => "{$method} ASC", 'limit' => 10), $options);
        $entities = $this->$object->findAll($condition, $options);
        $items = '';
        foreach($entities as $entity) $items.= "<li>{$entity->$method}</li>";
        $this->renderText("<ul>{$items}</ul>");
    }*/
    
    protected function defaultTemplatePath()
    {
        $module     = $this->request->module;
        $controller = $this->request->controller;
        $action     = $this->request->action;
        return $this->templatePath($module, $controller, $action);
    }
    
    protected function templatePath($module, $controller, $action)
    {
        return SContext::inclusionPath($module)."/views/$controller/$action.php";
    }
    
    protected function isPerformed()
    {
        return ($this->performedRender || $this->performedRedirect);
    }
    
    protected function requireModel($model)
    {
        if (class_exists($model)) return;
        
        if (!strpos($model, '/'))
            $module = $this->request->module;
        else
            list($module, $model) = explode('/', $model);
        
        $file = SContext::inclusionPath($module).'/models/'.strtolower($model).'.class.php';
        if (!file_exists($file)) throw new SException('Model not found : '.$model);
        require_once($file);
    }
    
    protected function requireHelper($helper)
    {
        if (!strpos($helper, '/'))
            $module = $this->request->module;
        else
            list($module, $helper) = explode('/', $helper);
        
        $file = SContext::inclusionPath($module)."/helpers/{$helper}helper.lib.php";
        if (!file_exists($file)) throw new SException('Helper not found : '.$helper);
        require_once($file);
    }
}

?>
