<input type='hidden' name='<?=$name?>' value='<?=$value?>' id='<?=$id?>' <?foreach ($attributes as $property_name=>$property_value): echo " {$property_name}='{$property_value}' "; endforeach;?>/>