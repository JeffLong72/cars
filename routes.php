<?php

// Render Twig template within container
$app->get('/admin', 'Controllers\\AdminController:index');
$app->post('/admin', 'Controllers\\AdminController:index');
$app->get('/admin/cars/all', 'Controllers\\AdminController:getCars');
$app->get('/admin/cars/add', 'Controllers\\AdminController:addCar');
$app->post('/admin/cars/add', 'Controllers\\AdminController:addCar');
$app->get('/admin/cars/edit/{id}', 'Controllers\\AdminController:editCar');
$app->post('/admin/cars/edit/{id}', 'Controllers\\AdminController:editCar');
$app->get('/admin/cars/update', 'Controllers\\AdminController:getApiData');
$app->get('/admin/logout', 'Controllers\\AdminController:doLogout');
