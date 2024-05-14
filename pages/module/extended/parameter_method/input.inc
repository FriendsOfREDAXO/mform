<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('System Elements with Parameters', MForm::factory()
        // use media field method with parameter property
        ->addMediaField(1, ['types'=>'png', 'preview'=>1, 'category'=>2, 'label'=>'Image'])
        // use set parameter method
        ->addMediaField(2)
            ->setLabel('Image')
            ->setParameter('preview', 1)
            ->setParameter('category', 2)
            ->setParameter('type', 'png')
        // use set parameters method
        ->addMediaField(3)
            ->setLabel('Image')
            ->setParameters(['types'=>'png', 'preview'=>1, 'category'=>2])
        // use media list method with parameter property
        ->addMedialistField(1, ['types'=>'gif,jpg', 'preview'=>1, 'category'=>4, 'label'=>'Image list'])
        // use set parameters for link field
        ->addLinkField(1)
            ->setParameters(['label'=>'Link', 'category'=>3])
        // and for linklist field
        ->addLinklistField(1, ['label'=>'Link list'])
            ->setParameter('category', 2)
    );
// parse form
echo $mform->show();