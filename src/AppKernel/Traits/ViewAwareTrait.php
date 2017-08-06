<?php

namespace AppKernel\Traits;

use Zend\View\Model\ViewModel;

trait ViewAwareTrait {
    // It is reused from parent where is calling this class.

    /**
     * @desc auto select view
     * @param type array $variables
     */
    public function display($variables = [], $terminal = false) {
        $params = $this->params();
        $controllers = explode('\\', $params->fromRoute('controller'));
        if (!isset($variables['template'])):
            $action = $params->fromRoute('action');
            $template = strtolower($controllers[1] . '/' . end($controllers) . '/' . $action . '.phtml');
        else:
            $template = strtolower($controllers[1] . '/') . $variables['template'];
            unset($variables['template']);
        endif;

        $viewModel = new ViewModel($variables);
        if ($terminal):
            $viewModel->setTerminal(true);
        endif;
        $viewModel->setTemplate($template);
        return $viewModel;
    }

}
