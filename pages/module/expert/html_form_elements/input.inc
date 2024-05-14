<?php
// show form elements
echo MForm::factory() // init mform
    // add fieldset area
    ->addFieldsetArea('HTML5 input elements', MForm::factory()
        // input color
        ->addInputField("color", "1.0", ['label'=>'Color field']) // use string for x.0 json values
        // input email
        ->addInputField("email", 1.1, ['label'=>'Email field'])
        // input url
        ->addInputField("url", 1.2, ['label'=>'URL field'])
        // input tel
        ->addInputField("tel", 1.3, ['label'=>'Tel field'])
        // input search
        ->addInputField("search", 1.4, ['label'=>'Search field'])
        // input number
        ->addInputField("number", 1.5, ['label'=>'Number field'])
        // input range
        ->addInputField("range", 1.6, ['label'=>'Range field'])
        // input range
        ->addInputField("range", 1.7, ['label'=>'Range field with datalist'])
            ->setOptions(array(1,"-20", 30, "-30"))
        // input datetime
        ->addInputField("datetime", 1.8, ['label'=>'Datetime field']) // Datum und Uhrzeit (mit Zeitzone)
        // input datetime-local
        ->addInputField("datetime-local", 1.9, ['label'=>'Datetime-local field']) // Datum und Uhrzeit (ohne Zeitzone)
        // input date
        ->addInputField("date", "2.date", ['label'=>'Date field']) // Datum
        // input time
        ->addInputField("time", "2.time", ['label'=>'Time field']) // Uhrzeit
        // input month
        ->addInputField("month", "2.month", ['label'=>'Month field']) // Monat
        // input week
        ->addInputField("week", "2.week", ['label'=>'Week field']) // Kalenderwoche
    )
    // parse form
    ->show();