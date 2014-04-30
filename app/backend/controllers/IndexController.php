<?php

namespace Baseapp\Backend\Controllers;

use Baseapp\Library\Auth;
use Baseapp\Library\I18n;

/**
 * Backend Index Controller
 *
 * @package     base-app
 * @category    Controller
 * @version     2.0
 */
class IndexController extends \Phalcon\Mvc\Controller
{

    public $scripts = array();

    /**
     * Before Action
     *
     * @package     base-app
     * @version     2.0
     */
    public function beforeExecuteRoute($dispatcher)
    {
        // Set default title
        $this->tag->setTitle('Index');

        // Add css and js to assets collection
        $this->assets->addCss('css/app.css');
        $this->assets->addJs('js/plugins/flashclose.js');
    }

    /**
     * Initialize
     *
     * @package     base-app
     * @version     2.0
     */
    public function initialize()
    {
        // Redirect to home page if user is not admin
        if (!Auth::instance()->logged_in('admin')) {
            $this->response->redirect('');
        }

        // Check the session lifetime
        if ($this->session->has('last_active') && time() - $this->session->get('last_active') > $this->config->session->options->lifetime) {
            $this->session->destroy();
        }

        $this->session->set('last_active', time());

        // Set the language from session
        if ($this->session->has('lang')) {
            I18n::instance()->lang($this->session->get('lang'));
            // Set the language from cookie
        } elseif ($this->cookies->has('lang')) {
            I18n::instance()->lang($this->cookies->get('lang')->getValue('string'));
        }

        // Send i18n, auth and langs to the view
        $this->view->setVars(array(
            'auth' => Auth::instance(),
            'i18n' => I18n::instance(),
            // Translate langs before
            'siteLangs' => array_map('__', $this->config->i18n->langs->toArray())
        ));
    }

    /**
     * Index Action
     *
     * @package     base-app
     * @version     2.0
     */
    public function indexAction()
    {
        $this->tag->setTitle(__('Admin panel'));
    }

    /**
     * After Action
     *
     * @package     base-app
     * @version     2.0
     */
    public function afterExecuteRoute($dispatcher)
    {
        // Set final title
        $this->tag->setTitleSeparator(' | ');
        $this->tag->appendTitle($this->config->app->name);

        // Set scripts
        $this->view->setVar('scripts', $this->scripts);

        // Minify css and js collection
        \Baseapp\Library\Tool::assetsMinification();
    }

    /**
     * Not found Action
     *
     * @package     base-app
     * @version     2.0
     */
    public function notfoundAction()
    {
        // Send a HTTP 404 response header
        $this->response->setStatusCode(404, "Not Found");
        $this->view->disableLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $this->view->setMainView('404');
        $this->assets->addCss('css/fonts.css');
    }

}
