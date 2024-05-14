<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // fieldset
    ->addFieldsetArea('Form elements with datalist', MForm::factory()
        // text with datalist
        ->addTextField(1, ['label'=>'Text input'])
            ->setOptions(['Apple', 'Orange', 'Peach', 'Melon', 'Strawberry'])
        // input range width datalist
        ->addInputField("range", 2, ['label'=>'Range field with datalist'])
            ->setAttribute("max",60)
            ->setAttribute("min",20)
            ->setOptions([10,20,30,40,50]) // to add datalist use set options
            ->setDefaultValue(30)
        // input datetime locale with datalist
        ->addInputField("datetime-local", 3, ['label'=>'Datetime-local field'])
            ->setOptions(["Santa Visit"=>"2012-12-24T23:59", "Chrismas party"=>"2012-12-25T18:00", "Happy New Year"=>"2013-01-01T00:00", "2012-12-30T00:00"]) // to add datalist use set options
        // input time with datalist
        ->addInputField("time", 4, ['label'=>'Time field'])
            ->setOption("00:00", "Midnight")
            ->setOption("06:00", 2)
            ->setOption("12:00", "Noon")
            ->setOption("18:00", 4)
    );
// parse form
echo $mform->show();