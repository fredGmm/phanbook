<?php
/**
 * Phanbook : Delightfully simple forum software
 *
 * Licensed under The GNU License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @link    http://phanbook.com Phanbook Project
 * @since   1.0.0
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */
namespace Phanbook\Common\Library\Events;

use Phalcon\Text;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phanbook\Models\Services\Service;
use Phanbook\Common\Library\Acl\Manager;

/**
 * \Phanbook\Common\Library\Events\AccessListener
 *
 * @package Phanbook\Common\Library\Events
 */
class AccessListener extends AbstractEvent
{
    /**
     * This action is executed before execute any action in the application.
     *
     * @param Event      $event      Event object.
     * @param Dispatcher $dispatcher Dispatcher object.
     * @param array      $data       The event data.
     *
     * @return mixed
     */
    public function beforeDispatch(Event $event, Dispatcher $dispatcher, array $data = null)
    {
        /** @var Service\User $userService */
        $userService = $this->getDI()->getShared(Service\User::class);

        /** @var Manager $aclManager */
        $aclManager = $this->getDI()->getShared('aclManager');

        $roles = $userService->getRoleNamesForCurrentViewer();
        $controller = $dispatcher->getControllerName();

        $protectedResource = $dispatcher->getModuleName() === 'backend' || Text::startsWith($controller, 'Admin', true);

        if ($protectedResource && !$aclManager->isAllowed($roles, Manager::ADMIN_AREA, 'access')) {
            $this->getDI()->getShared('eventsManager')->fire(
                'dispatch:beforeException',
                $dispatcher,
                new Dispatcher\Exception
            );
        }

        return !$event->isStopped();
    }
}