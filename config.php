<?php
	return [
		'mailer' => [
	        'smtp' => 'smtp.gmail.com',
	        'email' => 'testmail@gmail.com',
	        'password' => 'password',
	        'template_path' => __DIR__.'/templates/mails/',
	        'mail' => [
	            'sender_email' => "testmail@gmail.com",
	            'sender_name' => 'Test Mailer'
	        ]
    	],
    	'localize' => [
    		'text_domain' => 'test',
    		'locale_path' => __DIR__. '/locales/'
    	],
    	'locales' => [
	        'en' => 'en_US',
	        'ua' => 'uk_UA',
	        'ru' => 'ru_RU',
	        'be' => 'be',
	        'de' => 'de_DE',
	        'fr' => 'fr_FR'
    	]
	];