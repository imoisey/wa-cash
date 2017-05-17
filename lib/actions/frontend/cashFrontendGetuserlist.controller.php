<?php

class cashFrontendGetuserlistController extends waJsonController {

    public function execute() {
        $this->response = cashViewHelper::getUserListJSON('cash');
    }
}