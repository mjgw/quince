<?php

class Test extends QuinceBase{
    
    public function testMethod(){
        
        // echo "testMethod<br />";
        // return array();
        return "Blah";
        
    }
    
    public function otherMethod($get){
        
        // print_r($get);
        // echo "otherMethod<br />";
        $this->forward('test', 'testMethod');
        
    }
    
}