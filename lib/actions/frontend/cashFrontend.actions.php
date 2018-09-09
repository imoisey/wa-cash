<?php

class cashFrontendActions extends waViewActions {

    public function popupAction() {
        $start = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $finish = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('t'), date('Y')));
        $m_event_operation = new cashEventOperationModel();

        $sql_total = "SELECT SUM(amount) as total 
                    FROM cash_event_operation AS eo
                    LEFT JOIN cash_event AS e ON `e`.`id` = `eo`.`event_id`
                    WHERE pub_date > s:start AND pub_date < s:finish";
        $sql_awards = "SELECT SUM(amount) as award 
                    FROM cash_event_operation AS eo
                    LEFT JOIN cash_event AS e ON `e`.`id` = `eo`.`event_id`
                    WHERE `e`.`pub_date` > s:start AND `e`.`pub_date` < s:finish AND `eo`.`amount` > 0";
        $sql_fine = "SELECT SUM(amount) as fine 
                    FROM cash_event_operation AS eo
                    LEFT JOIN cash_event AS e ON `e`.`id` = `eo`.`event_id`
                    WHERE `e`.`pub_date` > s:start AND `e`.`pub_date` < s:finish AND `eo`.`amount` < 0";
        $total = $m_event_operation->query($sql_total, array('start' => $start, 'finish' => $finish))->fetch();
        $award = $m_event_operation->query($sql_awards, array('start' => $start, 'finish' => $finish))->fetch();
        $fine = $m_event_operation->query($sql_fine, array('start' => $start, 'finish' => $finish))->fetch();
        
        $this->view->assign('total', $total['total']);
        $this->view->assign('award', $award['award']);
        $this->view->assign('fine', $fine['fine']);
    }
    
}