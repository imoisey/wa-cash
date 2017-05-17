<?php

class cashFrontendGetuserController extends waJsonController {

    public function execute() {
        if(wa()->getUser()->isAuth()) {
            $user_id = wa()->getUser()->getId();
            $this->response = array(
                'user_id' => $user_id,
            );
        }
        return false;
    }
}