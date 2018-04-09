<?php
// Copyright 1999-2018. Plesk International GmbH. All rights reserved.

/**
 * Class Modules_WelcomeWp_ContentInclude
 */
class Modules_WelcomeWp_ContentInclude extends pm_Hook_ContentInclude
{
    public function init()
    {
        if (pm_Session::isExist()) {

            if (pm_Session::getClient()->isAdmin()) {
                $status = pm_Settings::get('active', 1);

                if (!empty($status)) {
                    $head = new Zend_View_Helper_HeadLink();
                    $head->headLink()->appendStylesheet(pm_Context::getBaseUrl() . 'styles.css');

                    $page_loaded = $_SERVER['REQUEST_URI'];
                    $white_list = Modules_WelcomeWp_Helper::getWhiteListPages();

                    if (Modules_WelcomeWp_Helper::addMessage()) {
                        if (in_array($page_loaded, $white_list)) {
                            $client_name = pm_Session::getClient()->getProperty('pname');

                            if (empty($client_name)) {
                                $client_name = pm_Session::getClient()->getProperty('login');
                            }

                            $content = pm_Locale::lmsg('message_introtext', [
                                'close'      => '/modules/welcome-wp/images/close.png',
                                'close_link' => pm_Context::getActionUrl('index', 'deactivate'),
                                'elvis'      => '/modules/welcome-wp/images/plesk_octopus_wp' . mt_rand(1, 2) . '.png',
                                'name'       => $client_name
                            ]);

                            if (Modules_WelcomeWp_Helper::checkAvailableDomains() == false) {
                                $content .= pm_Locale::lmsg('message_step_domain', [
                                    'link_domain' => '/admin/subscription/create'
                                ]);
                            } else {
                                $white_list_os = Modules_WelcomeWp_Helper::stepListOs();
                                $step = pm_Settings::get('welcome-step', 1);

                                if ($step == 1) {
                                    if (Modules_WelcomeWp_Helper::isInstalled('wp-toolkit')) {
                                        if (Modules_WelcomeWp_Helper::isInstalled('panel-migrator')) {
                                            $content .= pm_Locale::lmsg('message_step_install_full', [
                                                'link_install' => pm_Context::getActionUrl('index', 'redirect-custom-wp-install'),
                                                'link_migrate' => '/modules/panel-migrator/index.php/site-migration/new-migration'
                                            ]);
                                        } else {
                                            $content .= pm_Locale::lmsg('message_step_install_new', [
                                                'link_install'          => pm_Context::getActionUrl('index', 'redirect-custom-wp-install'),
                                                'link_install_migrator' => pm_Context::getActionUrl('index', 'install') . '?extension=panel-migrator'
                                            ]);
                                        }
                                    } else {
                                        $content .= pm_Locale::lmsg('message_step_install_not_wptoolkit', [
                                            'link_install' => Modules_WelcomeWp_Helper::getExtensionCatalogLink('wp-toolkit')
                                        ]);
                                    }

                                    if (in_array('security-advisor', $white_list_os)) {
                                        $content .= pm_Locale::lmsg('message_step_ssl_inactive', [
                                            'class' => 'todo'
                                        ]);
                                    }

                                    if (in_array('pagespeed-insights', $white_list_os)) {
                                        $content .= pm_Locale::lmsg('message_step_pagespeed_inactive', [
                                            'class' => 'todo'
                                        ]);
                                    }

                                    $content .= pm_Locale::lmsg('message_step_next', [
                                        'link_next'       => pm_Context::getActionUrl('index', 'step'),
                                        'link_deactivate' => pm_Context::getActionUrl('index', 'deactivate')
                                    ]);
                                } elseif ($step == 2) {
                                    if (in_array('wp-toolkit', $white_list_os)) {
                                        $content .= pm_Locale::lmsg('message_step_install_inactive', [
                                            'class' => 'complete'
                                        ]);
                                    }

                                    if (Modules_WelcomeWp_Helper::isInstalled('security-advisor')) {
                                        $content .= pm_Locale::lmsg('message_step_ssl', [
                                            'link_security' => '/modules/security-advisor/'
                                        ]);
                                    } else {
                                        $content .= pm_Locale::lmsg('message_step_ssl_not', [
                                            'link_install' => pm_Context::getActionUrl('index', 'install') . '?extension=security-advisor'
                                        ]);
                                    }

                                    if (in_array('pagespeed-insights', $white_list_os)) {
                                        $content .= pm_Locale::lmsg('message_step_pagespeed_inactive', [
                                            'class' => 'todo'
                                        ]);
                                    }

                                    $content .= pm_Locale::lmsg('message_step_next', [
                                        'link_next'       => pm_Context::getActionUrl('index', 'step'),
                                        'link_deactivate' => pm_Context::getActionUrl('index', 'deactivate')
                                    ]);
                                } elseif ($step == 3) {
                                    if (in_array('wp-toolkit', $white_list_os)) {
                                        $content .= pm_Locale::lmsg('message_step_install_inactive', [
                                            'class' => 'complete'
                                        ]);
                                    }

                                    if (in_array('security-advisor', $white_list_os)) {
                                        $content .= pm_Locale::lmsg('message_step_ssl_inactive', [
                                            'class' => 'complete'
                                        ]);
                                    }

                                    if (Modules_WelcomeWp_Helper::isInstalled('pagespeed-insights')) {
                                        $content .= pm_Locale::lmsg('message_step_pagespeed', [
                                            'link_pagespeed' => '/modules/pagespeed-insights/'
                                        ]);
                                    } else {
                                        $content .= pm_Locale::lmsg('message_step_pagespeed_not', [
                                            'link_install' => pm_Context::getActionUrl('index', 'install') . '?extension=pagespeed-insights'
                                        ]);
                                    }

                                    $content .= pm_Locale::lmsg('message_step_finish', [
                                        'link_finish' => pm_Context::getActionUrl('index', 'step'),
                                    ]);
                                } elseif ($step == 4) {
                                    $content .= pm_Locale::lmsg('message_step_restart', [
                                        'link_restart'    => pm_Context::getActionUrl('index', 'restart'),
                                        'link_deactivate' => pm_Context::getActionUrl('index', 'deactivate')
                                    ]);
                                }
                            }

                            $message = pm_Locale::lmsg('message_container', ['content' => $content]);

                            if (pm_View_Status::hasMessage($message) == false) {
                                pm_View_Status::addInfo($message, true);
                            }

                            pm_Settings::set('executed', time());
                        }
                    }
                }
            }
        }
    }
}
