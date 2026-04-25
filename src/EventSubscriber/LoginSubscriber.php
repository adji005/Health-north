<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(private RouterInterface $router) {}

    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'onLoginSuccess'];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            $url = $this->router->generate('admin_dashboard');
        } elseif (in_array('ROLE_DOCTOR', $roles)) {
            $url = $this->router->generate('doctor_dashboard');
        } else {
            $url = $this->router->generate('patient_dashboard');
        }

        $event->setResponse(new RedirectResponse($url));
    }
}