<?php

// takes a URI formatted host and validates it


class HTMLPurifier_AttrDef_URI_HostTest extends HTMLPurifier_AttrDefHarness
{

    function test() {

        $this->def = new HTMLPurifier_AttrDef_URI_Host();

        $this->assertDef('[2001:DB8:0:0:8:800:200C:417A]'); // IPv6
        $this->assertDef('124.15.6.89'); // IPv4
        $this->assertDef('www.google.com'); // reg-name

        // more domain name tests
        $this->assertDef('test.');
        $this->assertDef('sub.test.');
        $this->assertDef('.test', false);
        $this->assertDef('ff');
        $this->assertDef('1f', false);
        $this->assertDef('-f', false);
        $this->assertDef('f1');
        $this->assertDef('f-', false);
        $this->assertDef('sub.ff');
        $this->assertDef('sub.1f', false);
        $this->assertDef('sub.-f', false);
        $this->assertDef('sub.f1');
        $this->assertDef('sub.f-', false);
        $this->assertDef('ff.top');
        $this->assertDef('1f.top');
        $this->assertDef('-f.top', false);
        $this->assertDef('ff.top');
        $this->assertDef('f1.top');
        $this->assertDef('f-.top', false);

    }

}

// vim: et sw=4 sts=4
