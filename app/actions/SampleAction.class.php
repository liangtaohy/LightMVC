<?php

class SampleAction extends Action
{
    public function init($context)
    {

    }

    public function execute($context, $action_params = array())
    {
        // TODO: Implement execute() method.
        header('HTTP/1.1 200 Ok');
        header('status: 200 Ok');

        echo 'Hi,guy!';
        return true;
    }


}