<?php
namespace App\MyApplication

class MyClass 
{
    private $sytemUrl = null;
    public static const DEBUG = false;
    
    public function testFuction( $url )
    {
        $this->systeemUrlr = $url;
    }
    
    public function transformData( $rows )
    {
        foreach ($rows as $row) {
            // Logic here
        }
        
        $this->foo();
    }
}
