<?php
// Copyright 1999-2018. Plesk International GmbH. All rights reserved.

/**
 * Class IndexController
 */
class IndexController extends pm_Controller_Action
{
    protected $_accessLevel = 'admin';

    /**
     * Entry point to the extension page - indexAction
     */
    public function indexAction()
    {
        $form = new pm_Form_Simple();
        $form->addElement('checkbox', 'restart', [
            'label'   => $this->lmsg('form_restart_tutorial'),
            'value'   => '',
            'checked' => false
        ]);
        $form->addControlButtons();

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $restart = $form->getValue('restart');

            if (!empty($restart)) {
                $this->activateTutorial();
                $this->_status->addMessage('info', $this->lmsg('message_success_restart'));
            } else {
                $this->_status->addMessage('warning', $this->lmsg('message_error_restart'));
            }

            $this->_helper->json(['redirect' => pm_Context::getBaseUrl()]);
        }

        $this->view->form = $form;
    }

    /**
     * Reactivates the tutorial wizard
     */
    private function activateTutorial()
    {
        pm_Settings::set('active', 1);
        pm_Settings::set('welcome-step', 1);
    }

    /**
     * Saves the next step number as a setting value
     */
    public function stepAction()
    {
        $step = Modules_WelcomeWp_Helper::getNextStep();
        pm_Settings::set('welcome-step', $step);

        $this->redirect(Modules_WelcomeWp_Helper::getReturnUrl());
    }

    /**
     * Trigger action for installation of a transmitted extension
     */
    public function installAction()
    {
        if (!empty($_GET['extension'])) {
            $extension = htmlspecialchars($_GET['extension']);
            $result = Modules_WelcomeWp_Helper::installExtension($extension);

            if (is_string($result)) {
                $this->_status->addMessage('warning', $this->lmsg('message_error_install', [
                    'error' => $result
                ]));
            }
        }

        $this->redirect(Modules_WelcomeWp_Helper::getReturnUrl());
    }

    /**
     * Restarts the tutorial wizard - sets step to 1
     */
    public function restartAction()
    {
        pm_Settings::set('welcome-step', 1);

        $this->redirect(Modules_WelcomeWp_Helper::getReturnUrl());
    }

    /**
     * Deactivates the tutorial wizard
     */
    public function deactivateAction()
    {
        pm_Settings::set('active', 0);

        $this->redirect(Modules_WelcomeWp_Helper::getReturnUrl());
    }

    /**
     * Sets required session variable as a workaround for the WordPress Toolkit installation process
     */
    public function redirectCustomWpInstallAction()
    {
        $_SESSION['panel']['lastShownPanel'] = 'hosting';

        $this->redirect('/modules/wp-toolkit/index.php/domain/install', ['prependBase' => false]);
    }
}
