<?php

namespace InstantUssd;

use Bitmarshals\InstantUssd\UssdEventListener as InstantUssdEventListener;
use Bitmarshals\InstantUssd\UssdEvent;

/**
 * Description of UssdEventListener
 *
 * @author David Bwire
 */
class UssdEventListener extends InstantUssdEventListener {

    public function __construct(array $ussdMenusConfig) {
        parent::__construct($ussdMenusConfig);
        // HOME PAGE example
        // example - attaching an event
        $this->attach('Bitmarshals\InstantUssd', 'home_instant_ussd', function($e) use ($ussdMenusConfig) {
            if (!$e instanceof UssdEvent) {
                return false;
            }
            $menuConfig = $ussdMenusConfig[$e->getName()];
            if (!$e->containsIncomingData()) {
                $this->attachDynamicErrors($e, $menuConfig);
                // clear tracked menus
                $isValidResponse = $e->getParam('is_valid', true);
                if ($isValidResponse) {
                    // this method should only be called by home menus
                    $this->clearMenuVisitHistory($e);
                }
                return $this->ussdResponseGenerator->composeAndRenderUssdMenu($menuConfig, true, false);
            }
            // we have data sent in
            // 1. Get latest response; it should be valid
            $latestResponse = $e->getLatestResponse();
            $lastServedMenu = $e->getName();
            // 2. Do your processing; save to db; etc
            // 3. Determine next menu using $lastServedMenu, $menuConfig and $latestResponse
            return $this->ussdResponseGenerator->determineNextMenu($lastServedMenu, $menuConfig, $latestResponse);
        });
    }

}
