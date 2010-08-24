<?php


class IndexController extends Controller {
    function index() {
        $t = new Template('index');
        $t->show();
        
//        Mapper::get('Item', "header.title = 'test' ");
//        Mapper::get('Header', "items.title = 'another object' ");

        echo '<pre>';
        var_dump(Mapper::get('Header', "items.title = 'another object' "));
        die();


        
        // testing loading
//        echo '<pre>';
//        var_dump(Mapper::get_by_id('Header', 1));
//        die();
        

        /*
        // testing removing item 1
        $object = new Item();

        $object->id = 1;

        $object->title = 'is a test LOL';

        $object->uri = 'lol';


        Mapper::save($object);
        Mapper::delete($object);



        $object = new Item();

        $object->id = 2;

        $object->title = 'another object';

        Mapper::save($object);
        */

        
        // testing cache in item # 2
        /*
        $object = Mapper::get_by_id('Item', 2);
        $object->description = ':(';
        echo '<pre>';
        var_dump(Mapper::get_by_id('Item', 2));
        */
        
        // testing relationships
        $header = new Header();
        $header->id = 1;
        $header->title = 'test';
        
        $object = new Item();

        $object->id = 1;

        $object->title = 'an object';
        
        $header->items[] = $object;
        
        $object = new Item();

        $object->id = 2;

        $object->title = 'another object';
        
        $header->items[] = $object;

        Mapper::save($header);

//        Mapper::delete($header);

    }
}
?>