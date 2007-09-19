<?php

class ezcWebdavPropertyStorageTest extends ezcTestCase
{
    public static function suite()
    {
		return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    public function testAttachLiveProperty()
    {
        $storage = new ezcWebdavPropertyStorage();
        $prop    = new ezcWebdavGetContentLengthProperty();

        $storage->attach( $prop );

        $this->assertAttributeEquals(
            array(
                'DAV:' => array(
                    'getcontentlength' => $prop,
                ),
            ),
            'properties',
            $storage
        );
    }

    public function testAttachDeadProperty()
    {
        $storage         = new ezcWebdavPropertyStorage();
        $prop            = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $prop );

        $this->assertAttributeEquals(
            array(
                'http://example.com/foo/bar' => array(
                    'foobar' => $prop,
                ),
            ),
            'properties',
            $storage
        );
    }

    public function testAttachMultipleProperties()
    {
        $storage  = new ezcWebdavPropertyStorage();
        $liveProp = new ezcWebdavGetContentLengthProperty();
        $deadProp = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $liveProp );
        $storage->attach( $deadProp );

        $this->assertAttributeEquals(
            array(
                'DAV:' => array(
                    'getcontentlength' => $liveProp,
                ),
                'http://example.com/foo/bar' => array(
                    'foobar' => $deadProp,
                ),
            ),
            'properties',
            $storage
        );
    }

    public function testAttacheMultiplePropertiesOverwrite()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $liveProp  = new ezcWebdavGetContentLengthProperty();
        $liveProp2 = new ezcWebdavGetContentLengthProperty();
        $deadProp  = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );
        $deadProp2 = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... other content' );

        $storage->attach( $liveProp );
        $storage->attach( $deadProp );

        $this->assertAttributeEquals(
            array(
                'DAV:' => array(
                    'getcontentlength' => $liveProp,
                ),
                'http://example.com/foo/bar' => array(
                    'foobar' => $deadProp,
                ),
            ),
            'properties',
            $storage
        );

        $storage->attach( $liveProp2 );
        $storage->attach( $deadProp2 );

        $this->assertAttributeEquals(
            array(
                'DAV:' => array(
                    'getcontentlength' => $liveProp2,
                ),
                'http://example.com/foo/bar' => array(
                    'foobar' => $deadProp2,
                ),
            ),
            'properties',
            $storage
        );
    }

    public function testDetachLiveProperty()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $liveProp  = new ezcWebdavGetContentLengthProperty();

        $storage->attach( $liveProp );

        $this->assertAttributeEquals(
            array(
                'DAV:' => array(
                    'getcontentlength' => $liveProp,
                ),
            ),
            'properties',
            $storage
        );

        $storage->detach( 'getcontentlength' );

        $this->assertAttributeEquals(
            array(
                'DAV:' => array(),
            ),
            'properties',
            $storage
        );
    }

    public function testDetachDeadProperty()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $deadProp  = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $deadProp );

        $this->assertAttributeEquals(
            array(
                'http://example.com/foo/bar' => array(
                    'foobar' => $deadProp,
                ),
            ),
            'properties',
            $storage
        );

        $storage->detach( 'foobar', 'http://example.com/foo/bar' );

        $this->assertAttributeEquals(
            array(
                'http://example.com/foo/bar' => array()
            ),
            'properties',
            $storage
        );
    }

    public function testDetachMultipleProperties()
    {
        $storage  = new ezcWebdavPropertyStorage();
        $liveProp = new ezcWebdavGetContentLengthProperty();
        $deadProp = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $liveProp );
        $storage->attach( $deadProp );

        $this->assertAttributeEquals(
            array(
                'DAV:' => array(
                    'getcontentlength' => $liveProp,
                ),
                'http://example.com/foo/bar' => array(
                    'foobar' => $deadProp,
                ),
            ),
            'properties',
            $storage
        );
        
        $storage->detach( 'getcontentlength' );
        $storage->detach( 'foobar', 'http://example.com/foo/bar' );
 
        $this->assertAttributeEquals(
            array(
                'DAV:' => array(
                ),
                'http://example.com/foo/bar' => array(
                ),
            ),
            'properties',
            $storage
        );
    }
    
    public function testContainsLiveProperty()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $liveProp  = new ezcWebdavGetContentLengthProperty();

        $storage->attach( $liveProp );
        
        $this->assertTrue(
            $storage->contains( 'getcontentlength' )
        );
        $this->assertFalse(
            $storage->contains( 'foobar', 'http://example.com/foo/bar' )
        );
    }
    
    public function testContainsDeadProperty()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $deadProp  = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $deadProp );
        
        $this->assertFalse(
            $storage->contains( 'getcontentlength' )
        );
        $this->assertTrue(
            $storage->contains( 'foobar', 'http://example.com/foo/bar' )
        );
    }

    public function testContainsMultipleProperties()
    {
        $storage  = new ezcWebdavPropertyStorage();
        $liveProp = new ezcWebdavGetContentLengthProperty();
        $deadProp = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $liveProp );
        $storage->attach( $deadProp );
        
        $this->assertTrue(
            $storage->contains( 'getcontentlength' )
        );
        $this->assertTrue(
            $storage->contains( 'foobar', 'http://example.com/foo/bar' )
        );
    }

    public function testGetLivePropertySuccess()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $liveProp  = new ezcWebdavGetContentLengthProperty();

        $storage->attach( $liveProp );
        
        $this->assertSame(
            $liveProp,
            $storage->get( 'getcontentlength' )
        );
    }

    public function testGetLivePropertyFailure()
    {
        $storage   = new ezcWebdavPropertyStorage();
        
        $this->assertNull(
            $storage->get( 'getcontentlength' )
        );
    }

    public function testGetDeadPropertySuccess()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $deadProp  = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $deadProp );
        
        $this->assertSame(
            $deadProp,
            $storage->get( 'foobar', 'http://example.com/foo/bar' )
        );
    }

    public function testGetDeadPropertyFailure()
    {
        $storage   = new ezcWebdavPropertyStorage();
        
        $this->assertNull(
            $storage->get( 'foobar', 'http://example.com/foo/bar' )
        );
    }

    public function testGetLivePropertiesExistent()
    {
        $storage = new ezcWebdavPropertyStorage();
        $prop    = new ezcWebdavGetContentLengthProperty();

        $storage->attach( $prop );

        $this->assertEquals(
            array(
                'getcontentlength' => $prop,
            ),
            $storage->getProperties()
        );
    }

    public function testGetDeadPropertiesExistent()
    {
        $storage         = new ezcWebdavPropertyStorage();
        $prop            = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $prop );

        $this->assertEquals(
            array(
                'foobar' => $prop,
            ),
            $storage->getProperties( 'http://example.com/foo/bar' )
        );
    }

    public function testGetLivePropertiesNonExistent()
    {
        $storage = new ezcWebdavPropertyStorage();

        $this->assertEquals(
            array(),
            $storage->getProperties()
        );
    }

    public function testGetDeadPropertiesNonExistent()
    {
        $storage         = new ezcWebdavPropertyStorage();

        $this->assertEquals(
            array(),
            $storage->getProperties( 'http://example.com/foo/bar' )
        );
    }

    public function testGetMultiplePropertiesExistent()
    {
        $storage  = new ezcWebdavPropertyStorage();
        $liveProp = new ezcWebdavGetContentLengthProperty();
        $deadProp = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $liveProp );
        $storage->attach( $deadProp );

        $this->assertEquals(
            array(
                'getcontentlength' => $liveProp,
            ),
            $storage->getProperties()
        );
        $this->assertEquals(
            array(
                'foobar' => $deadProp,
            ),
            $storage->getProperties( 'http://example.com/foo/bar' )
        );
    }

    public function testGetAllPropertiesLiveProperty()
    {
        $storage = new ezcWebdavPropertyStorage();
        $prop    = new ezcWebdavGetContentLengthProperty();

        $storage->attach( $prop );

        $this->assertEquals(
            array(
                'DAV:' => array(
                    'getcontentlength' => $prop,
                ),
            ),
            $storage->getAllProperties()
        );
    }

    public function testGetAllPropertiesDeadProperty()
    {
        $storage         = new ezcWebdavPropertyStorage();
        $prop            = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $prop );

        $this->assertEquals(
            array(
                'http://example.com/foo/bar' => array(
                    'foobar' => $prop,
                ),
            ),
            $storage->getAllProperties()
        );
    }

    public function testGetAllPropertiesMultipleProperties()
    {
        $storage  = new ezcWebdavPropertyStorage();
        $liveProp = new ezcWebdavGetContentLengthProperty();
        $deadProp = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );

        $storage->attach( $liveProp );
        $storage->attach( $deadProp );

        $this->assertEquals(
            array(
                'DAV:' => array(
                    'getcontentlength' => $liveProp,
                ),
                'http://example.com/foo/bar' => array(
                    'foobar' => $deadProp,
                ),
            ),
            $storage->getAllProperties()
        );

        $this->assertEquals(
            2,
            count( $storage ),
            'Expected property count is: two.'
        );
    }

    public function testGetAllPropertiesNone()
    {
        $storage  = new ezcWebdavPropertyStorage();

        $this->assertEquals(
            array(),
            $storage->getAllProperties()
        );
    }

    public function testPropertyStorageDiff()
    {
        $liveProp  = new ezcWebdavGetContentLengthProperty();
        $deadProp  = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );
        $deadProp2 = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'blubb', 'some... content' );

        $storage1 = new ezcWebdavPropertyStorage();
        $storage1->attach( $liveProp );
        $storage1->attach( $deadProp );

        $storage2 = new ezcWebdavPropertyStorage();
        $storage2->attach( $deadProp );
        $storage2->attach( $deadProp2 );

        $diff = $storage1->diff( $storage2 );

        $this->assertEquals(
            array(
                'DAV:' => array(
                    'getcontentlength' => $liveProp,
                ),
            ),
            $diff->getAllProperties()
        );
    }

    public function testPropertyStorageIntersection()
    {
        $liveProp  = new ezcWebdavGetContentLengthProperty();
        $deadProp  = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some... content' );
        $deadProp2 = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'blubb', 'some... content' );

        $storage1 = new ezcWebdavPropertyStorage();
        $storage1->attach( $liveProp );
        $storage1->attach( $deadProp );

        $storage2 = new ezcWebdavPropertyStorage();
        $storage2->attach( $deadProp );
        $storage2->attach( $deadProp2 );

        $intersection = $storage1->intersect( $storage2 );

        $this->assertEquals(
            array(
                'http://example.com/foo/bar' => array(
                    'foobar' => $deadProp,
                ),
            ),
            $intersection->getAllProperties()
        );
    }

    public function testIteratorPreserveOrder()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $orderedProperties = array();
        $storage->attach(
            $orderedProperties[] = new ezcWebdavGetContentLengthProperty()
        );
        $storage->attach(
            $orderedProperties[] = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some content' )
        );
        $storage->attach(
            $orderedProperties[] = new ezcWebdavGetContentTypeProperty()
        );

        $nr = 0;
        foreach ( $storage as $proprety )
        {
            $this->assertEquals(
                $proprety,
                $orderedProperties[$nr],
                "Property on position $nr does not match."
            );

            ++$nr;
        }
    }

    public function testIteratorPreserveOrderDetachFirst()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $orderedProperties = array();
        $storage->attach(
            new ezcWebdavGetContentLengthProperty()
        );
        $storage->attach(
            $orderedProperties[] = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some content' )
        );
        $storage->attach(
            $orderedProperties[] = new ezcWebdavGetContentTypeProperty()
        );

        $storage->detach( 'getcontentlength', 'DAV:' );

        $nr = 0;
        foreach ( $storage as $proprety )
        {
            $this->assertEquals(
                $proprety,
                $orderedProperties[$nr],
                "Property on position $nr does not match."
            );

            ++$nr;
        }
    }

    public function testIteratorPreserveOrderDetachLast()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $orderedProperties = array();
        $storage->attach(
            $orderedProperties[] = new ezcWebdavGetContentLengthProperty()
        );
        $storage->attach(
            $orderedProperties[] = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some content' )
        );
        $storage->attach(
            new ezcWebdavGetContentTypeProperty()
        );

        $storage->detach( 'getcontenttype', 'DAV:' );

        $nr = 0;
        foreach ( $storage as $proprety )
        {
            $this->assertEquals(
                $proprety,
                $orderedProperties[$nr],
                "Property on position $nr does not match."
            );

            ++$nr;
        }
    }

    public function testIteratorPreserveOrderDetachAndReattach()
    {
        $storage   = new ezcWebdavPropertyStorage();
        $orderedProperties = array();
        $storage->attach(
            $orderedProperties[] = new ezcWebdavGetContentLengthProperty()
        );
        $storage->attach(
            new ezcWebdavGetContentTypeProperty()
        );
        $storage->attach(
            new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some content' )
        );
        $storage->attach(
            $orderedProperties[] = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'foobar', 'some other content' )
        );
        $storage->attach(
            $orderedProperties[] = new ezcWebdavDeadProperty( 'http://example.com/foo/bar', 'blubb', 'some content' )
        );

        $storage->detach( 'getcontenttype', 'DAV:' );

        $nr = 0;
        foreach ( $storage as $proprety )
        {
            $this->assertEquals(
                $proprety,
                $orderedProperties[$nr],
                "Property on position $nr does not match."
            );

            ++$nr;
        }
    }
}

?>