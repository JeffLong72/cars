<?php

namespace Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Class AdminController
 *
 * @package Controllers
 */
class AdminController
{
    /**
     * @var \Slim\Container Stores the container for dependency purposes.
     */
    protected $container;
	
    /**
     * @var Stores the path to the image upload folder
     */	
	protected $upload_folder;

    /**
     * Store the container during class construction.
     *
     * @param \Slim\Container $container
     */
    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
		$this->upload_folder = dirname(dirname(__FILE__))."/assets/images";
    }

    /**
     * Admin login screen
	 * Url: [site-url]/admin
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Array    $args
     * @return mixed
     */
    public function index(Request $request, Response $response, $args)
    {
		// get post data
		$form = (!empty($_POST['form'])) ? $_POST['form'] : "";
		
		// if we have form data
		if(!empty($form)) 
		{ 
			// strip html from the form fields
			$form = preg_replace("/<.+>/sU", "", $form);
	
			// validate that all fields have data
			$form_valid = TRUE;
			foreach($form as $key => $value) 
			{
				if(empty($value)) 
				{
					$form_valid = FALSE;
				}
			}
			
			// if any field(s) are empty
			if(!$form_valid) 
			{
				$message = "Please enter all fields";
			}
			else 
			{
				// get pdo object
				$pdo = $this->container['db'];
				
				// get cars from database table
				$stmt = $pdo->prepare('SELECT * 
										FROM admin_users 
										WHERE username = :username');
				$stmt->execute([ 'username' => $form['username'] ]);
				$result = $stmt->fetchAll();

				// do we have a valid username and is password correct?
				// ( if not, we wont say which value is wrong as this helps hackers to guess the correct login details )
				if ( empty($result[0]['password']) || ! password_verify($form['password'], $result[0]['password']) ) 
				{
					$message = 'Incorrect login details';
				}
				else 
				{
					// set login session
					$_SESSION['admin_logged_in'] = 1;
					
					// redirect admin to all cars
					header('location: /admin/cars/all');
					exit();
				}
			}
		}
		
		// send data to template
        return $this->container->get('view')->render(
            $response, 'admin-login.twig', [
			 'username' => (!empty($form['username'])) ? $form['username'] : "",
			 'password' => (!empty($form['password'])) ? $form['password'] : "",
			 'message' =>  (!empty($message)) ? $message : "",
			]
        );
    }
	
    /**
     * Display all cars
	 * Url: [site-url]/admin/cars/all
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Array    $args
     * @return mixed
     */
    public function getCars(Request $request, Response $response, $args)
    {
		// if admin is not logged in, he/she shouldnt be here!
		$this->checkLogin();

		// get pdo object
		$pdo = $this->container['db'];
		
		// get cars from database table
		$stmt = $pdo->prepare('SELECT t1.*, t2.image 
								FROM vehicles as t1
								LEFT JOIN vehicles_images as t2 ON t1.model_id = t2.model_id
								ORDER BY t1.model_make_id, t1.model_id ASC');
		$stmt->execute();
		$result = $stmt->fetchAll();

		// send data to template
        return $this->container->get('view')->render(
            $response, 'admin-cars-all.twig', [
			 'cars' => $result
			]
        );
    }

    /**
     * Add a new car
	 * Url: [site-url]/admin/cars/add
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Array    $args
     * @return mixed
     */
    public function addCar(Request $request, Response $response, $args)
    {
		// if admin is not logged in, he/she shouldnt be here!
		$this->checkLogin();

		// get pdo object
		$pdo = $this->container['db'];

		// get post data
		$form = (!empty($_POST['form'])) ? $_POST['form'] : "";
		
		// if we have form data
		if(!empty($form)) 
		{ 
			// strip html from the form fields
			$form = preg_replace("/<.+>/sU", "", $form);
			
			// validate that all fields have data
			$form_valid = TRUE;
			foreach($form as $key => $value) 
			{
				if(empty($value)) 
				{
					$form_valid = FALSE;
				}
			}
			$message = (!$form_valid) ? "Please enter all fields" : "";
			
			// where data must be specific format, check these fields have valid data
			switch($form) {
				case (!is_numeric($form['model_id'])):
					$form_valid = FALSE;
					$message = "Please enter a model id (numeric values only, eg. 123)";
					break;
				case (!is_numeric($form['model_year'])):
					$form_valid = FALSE;
					$message = "Please enter a year (numeric values only, eg. 1989)";
					break;
				case (!is_numeric($form['price'])):
					$form_valid = FALSE;
					$message = "Please enter a price (numeric values only, eg. 10000)";
					break;
				case (!is_numeric($form['model_weight_kg'])):
					$form_valid = FALSE;
					$message = "Please enter a weight (numeric values only, eg. 3000)";
					break;
			}

			// does this model id already exist?
			$stmt = $pdo->prepare('SELECT model_id FROM vehicles WHERE model_id = :model_id LIMIT 1');
			$stmt->execute([ 'model_id' => (int) $form['model_id'] ]);
			$result = $stmt->fetch();
			if($result) 
			{
				$form_valid = FALSE;
				$message = "Model id already exists, please enter a different value";				
			}
	
			// add car
			if($form_valid) 
			{
				// insert into db
				try{ 
					// prepare statement
					$stmt = $pdo->prepare('INSERT INTO vehicles 
														(
															model_id,
															model_make_id,
															model_name,
															model_trim,
															model_year,
															model_body,
															model_engine_position,
															model_engine_type,
															model_engine_compression,
															model_engine_fuel,
															make_country,
															model_weight_kg,
															model_transmission_type,
															price
														)
														VALUES 
														(
															:model_id,
															:model_make_id,
															:model_name,
															:model_trim,
															:model_year,
															:model_body,
															:model_engine_position,
															:model_engine_type,
															:model_engine_compression,
															:model_engine_fuel,
															:make_country,
															:model_weight_kg,
															:model_transmission_type,
															:price
														)
													');
					// execute_statement							
					$stmt->execute([
								'model_id' => $form['model_id'],
								'model_make_id' => $form['model_make_id'],
								'model_name' => $form['model_name'],
								'model_trim' => $form['model_trim'],
								'model_year' => $form['model_year'],
								'model_body' => $form['model_body'],
								'model_engine_position' => $form['model_engine_position'],
								'model_engine_type' => $form['model_engine_type'],
								'model_engine_compression' => $form['model_engine_compression'],
								'model_engine_fuel' => $form['model_engine_fuel'],
								'make_country' => $form['make_country'],
								'model_weight_kg' => $form['model_weight_kg'],
								'model_transmission_type' => $form['model_transmission_type'],
								'price' => $form['price']
							]);	

					$message  = "Success! Car details have been updated";
							
				} 
				catch(PDOException $exception){ 
					// show PDO error message
					print_r($exception->getMessage()); 
				}			

				// add car image to database table
				if(!empty($_FILES['form'])) 
				{
					// is admin uploading an image?
					if($_FILES['form']['error']['file'] != 4) 
					{
						// set error status
						$upload_error = FALSE;
						
						// array of valid file extensions
						$valid_file_ext = array(".jpg", ".png");
						
						// array of valid file mime
						$valid_file_type = array("image/jpeg", "image/pjpeg", "image/png");
						
						// maximum file size
						$maximum_file_size = 30000;
						
						// check file ext is valid
						$uploaded_file_extension = substr($_FILES['form']['name']['file'], -4);
						if(!in_array($uploaded_file_extension, $valid_file_ext)) 
						{
							$upload_error = TRUE;
							$message = "Invalid file extension, must be ".implode(", ",$valid_file_ext);
						}
						
						// check file type is valid
						if(!in_array($_FILES['form']['type']['file'], $valid_file_type) && !$upload_error) 
						{
							$upload_error = TRUE;
							$message = "Invalid file type, must be ".implode(", ",$valid_file_ext);
						}

						// check file size is valid
						if($_FILES['form']['size']['file'] > $maximum_file_size && !$upload_error) 
						{
							$upload_error = TRUE;
							$message = "Invalid file size, maximum size ".$maximum_file_size." bytes";
						}	

						// if we have no upload errors
						if(!$upload_error) 
						{
							// basename() may prevent filesystem traversal attacks;
							// https://php.net/manual/en/function.move-uploaded-file.php
							$tmp_name = $_FILES['form']['tmp_name']['file'];
							$name = basename($_FILES['form']['name']['file']);
							// upload the file to the images folder
							move_uploaded_file($tmp_name, $this->upload_folder."/".$form['model_id']."_".$name);
							
							// create thumbnail of image
							$this->createThumb($this->upload_folder."/".$form['model_id']."_".$name, $this->upload_folder."/thumbnails/".$form['model_id']."_".$name, 100);
							
							// save image in db table
							try{ 
								// prepare statement
								$stmt = $pdo->prepare('REPLACE INTO vehicles_images
														(
															model_id,
															image
														)
														VALUES 
														(
															:model_id,
															:image
														)
													');
								// execute_statement							
								$stmt->execute([
										'model_id' => $form['model_id'],
										'image' => $form['model_id']."_".$name
									]);					
							} 
							catch(PDOException $exception){ 
								// show PDO error message
								print_r($exception->getMessage()); 
							}						
							$message  = "Success! Car details have been updated";
						}
					}
					else 
					{
						// no file has been uploaded 
						// ( so we dont need to do anything here )
					}
				}
			}
		}
		
		// send data to template
        return $this->container->get('view')->render(
            $response, 'admin-cars-add.twig', [
			 'message' => (!empty($message)) ? $message : "",
			 'car' => (!empty($form)) ? $form : "",
			]
        );
    }

    /**
     * Edit a car 
	 * Url: [site-url]/admin/cars/edit/[id]
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Array    $args
     * @return mixed
     */
    public function editCar(Request $request, Response $response, $args)
    {
		// if admin is not logged in, he/she shouldnt be here!
		$this->checkLogin();
		
		// get pdo object
		$pdo = $this->container['db'];

		// get post data
		$form = (!empty($_POST['form'])) ? $_POST['form'] : "";
		
		// if we have form data
		if(!empty($form)) 
		{ 
			// strip html from the form fields
			$form = preg_replace("/<.+>/sU", "", $form);
	
			// validate that all fields have data
			$form_valid = TRUE;
			foreach($form as $key => $value) 
			{
				if(empty($value)) 
				{
					$form_valid = FALSE;
				}
			}
			$message = (!$form_valid) ? "Please enter all fields" : "";
			
			// where data must be specific format, check these fields have valid data
			switch($form) {
				case (!is_numeric($form['model_id'])):
					$form_valid = FALSE;
					$message = "Please enter a model id (numeric values only, eg. 123)";
					break;
				case (!is_numeric($form['model_year'])):
					$form_valid = FALSE;
					$message = "Please enter a year (numeric values only, eg. 1989)";
					break;
				case (!is_numeric($form['price'])):
					$form_valid = FALSE;
					$message = "Please enter a price (numeric values only, eg. 10000)";
					break;
				case (!is_numeric($form['model_weight_kg'])):
					$form_valid = FALSE;
					$message = "Please enter a weight (numeric values only, eg. 3000)";
					break;
			}

			// update car
			if($form_valid) 
			{
				// insert into db
				try{ 
					// prepare statement
					$stmt = $pdo->prepare('REPLACE INTO vehicles 
														(
															model_id,
															model_make_id,
															model_name,
															model_trim,
															model_year,
															model_body,
															model_engine_position,
															model_engine_type,
															model_engine_compression,
															model_engine_fuel,
															make_country,
															model_weight_kg,
															model_transmission_type,
															price
														)
														VALUES 
														(
															:model_id,
															:model_make_id,
															:model_name,
															:model_trim,
															:model_year,
															:model_body,
															:model_engine_position,
															:model_engine_type,
															:model_engine_compression,
															:model_engine_fuel,
															:make_country,
															:model_weight_kg,
															:model_transmission_type,
															:price
														)
													');
					// execute_statement							
					$stmt->execute([
								'model_id' => $form['model_id'],
								'model_make_id' => $form['model_make_id'],
								'model_name' => $form['model_name'],
								'model_trim' => $form['model_trim'],
								'model_year' => $form['model_year'],
								'model_body' => $form['model_body'],
								'model_engine_position' => $form['model_engine_position'],
								'model_engine_type' => $form['model_engine_type'],
								'model_engine_compression' => $form['model_engine_compression'],
								'model_engine_fuel' => $form['model_engine_fuel'],
								'make_country' => $form['make_country'],
								'model_weight_kg' => $form['model_weight_kg'],
								'model_transmission_type' => $form['model_transmission_type'],
								'price' => $form['price']
							]);	

					$message  = "Success! Car details have been updated";
							
				} 
				catch(PDOException $exception){ 
					// show PDO error message
					print_r($exception->getMessage()); 
				}			

				// add car image to database table
				if(!empty($_FILES['form'])) 
				{
					// is admin uploading an image?
					if($_FILES['form']['error']['file'] != 4) 
					{
						// set error status
						$upload_error = FALSE;
						
						// array of valid file extensions
						$valid_file_ext = array(".jpg", ".png");
						
						// array of valid file mime
						$valid_file_type = array("image/jpeg", "image/pjpeg", "image/png");
						
						// maximum file size
						$maximum_file_size = 30000;
						
						// check file ext is valid
						$uploaded_file_extension = substr($_FILES['form']['name']['file'], -4);
						if(!in_array($uploaded_file_extension, $valid_file_ext)) 
						{
							$upload_error = TRUE;
							$message = "Invalid file extension, must be ".implode(", ",$valid_file_ext);
						}
						
						// check file type is valid
						if(!in_array($_FILES['form']['type']['file'], $valid_file_type) && !$upload_error) 
						{
							$upload_error = TRUE;
							$message = "Invalid file type, must be ".implode(", ",$valid_file_ext);
						}

						// check file size is valid
						if($_FILES['form']['size']['file'] > $maximum_file_size && !$upload_error) 
						{
							$upload_error = TRUE;
							$message = "Invalid file size, maximum size ".$maximum_file_size." bytes";
						}	

						// if we have no upload errors
						if(!$upload_error) 
						{
							// basename() may prevent filesystem traversal attacks;
							// https://php.net/manual/en/function.move-uploaded-file.php
							$tmp_name = $_FILES['form']['tmp_name']['file'];
							$name = basename($_FILES['form']['name']['file']);
							// upload the file to the images folder
							move_uploaded_file($tmp_name, $this->upload_folder."/".$form['model_id']."_".$name);
							
							// create thumbnail of image
							$this->createThumb($this->upload_folder."/".$form['model_id']."_".$name, $this->upload_folder."/thumbnails/".$form['model_id']."_".$name, 100);
							
							// save image in db table
							try{ 
								// prepare statement
								$stmt = $pdo->prepare('REPLACE INTO vehicles_images
														(
															model_id,
															image
														)
														VALUES 
														(
															:model_id,
															:image
														)
													');
								// execute_statement							
								$stmt->execute([
										'model_id' => $form['model_id'],
										'image' => $form['model_id']."_".$name
									]);					
							} 
							catch(PDOException $exception){ 
								// show PDO error message
								print_r($exception->getMessage()); 
							}						
							$message  = "Success! Car details have been updated";
						}
					}
					else 
					{
						// no file has been uploaded 
						// ( so we dont need to do anything here )
					}
				}
			}
		}
		
		// get cars from database table
		$stmt = $pdo->prepare('SELECT t1.*, t2.image 
								FROM vehicles as t1
								LEFT JOIN vehicles_images as t2 ON t1.model_id = t2.model_id
								WHERE t1.model_id = :model_id LIMIT 1');
		$stmt->execute([ 'model_id' => (int) $args['id'] ]);
		$result = $stmt->fetch();
		
		// if we have a form value that has NOT been inserted into the database 
		// (eg. because of a form error) we need to merge the form data and result data
		// together so the admin wont have to re-add the data each time the form submission fails
		$result = (!empty($form)) ? array_merge($result,$form) : $result;
		
		// send data to template
        return $this->container->get('view')->render(
            $response, 'admin-cars-edit.twig', [
			 'id' => (int) $args['id'],
			 'message' => (!empty($message)) ? $message : "",
			 'car' => (!empty($result)) ? $result : ""
			]
        );
    }

    /**
     * Get all cars from 3rd party api 
	 * The recommended API URL is not valid JSON so you cannot use,
	 * http://www.carqueryapi.com/api/0.3/?callback=&cmd=getTrims
	 * You need to use,
	 * http://www.carqueryapi.com/api/0.3/?cmd=getTrims
	 * Ref: https://stackoverflow.com/questions/28217745/json-returns-null-value
     */
    public function getApiData()
    {
		// if admin is not logged in, stop here...
		if(!isset($_SESSION['admin_logged_in'])) {
			exit();
		}

		// url to 3rd party car api
		$url = "http://www.carqueryapi.com/api/0.3/?cmd=getTrims";
		
		// get data using curl
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url ); 
		curl_setopt($ch, CURLOPT_POST, 1 ); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, ''); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$result = curl_exec($ch);
		
		// display curl errors
		if (curl_errno($ch)) { 
		   print curl_error($ch); 
		} 
		
		// close curl
		curl_close($ch); 
		
		// if we have data from the api
		if(!empty($result)) 
		{
			// set time limit to execute script
			set_time_limit(300);
			
			// get pdo object
			$pdo = $this->container['db'];
			
			// decode the json object into PHP array	
			$cars = json_decode($result);
			
			foreach($cars->Trims as $c) 
			{
				// insert into db
				try{ 
					// prepare statement
					$stmt = $pdo->prepare('REPLACE INTO vehicles 
													(
														model_id,
														model_make_id,
														model_name,
														model_trim,
														model_year,
														model_body,
														model_engine_position,
														model_engine_type,
														model_engine_compression,
														model_engine_fuel,
														make_country,
														model_weight_kg,
														model_transmission_type,
														price
													)
													VALUES 
													(
														:model_id,
														:model_make_id,
														:model_name,
														:model_trim,
														:model_year,
														:model_body,
														:model_engine_position,
														:model_engine_type,
														:model_engine_compression,
														:model_engine_fuel,
														:make_country,
														:model_weight_kg,
														:model_transmission_type,
														:price
													)
												');
					// execute_statement							
					$stmt->execute([
							'model_id' => $c->model_id,
							'model_make_id' => $c->model_make_id,
							'model_name' => $c->model_name,
							'model_trim' => $c->model_trim,
							'model_year' => $c->model_year,
							'model_body' => $c->model_body,
							'model_engine_position' => $c->model_engine_position,
							'model_engine_type' => $c->model_engine_type,
							'model_engine_compression' => $c->model_engine_compression,
							'model_engine_fuel' => $c->model_engine_fuel,
							'make_country' => $c->make_country,
							'model_weight_kg' => $c->model_weight_kg,
							'model_transmission_type' => $c->model_transmission_type,
							'price' => rand(1999, 19999)
						]);					
				} 
				catch(PDOException $exception){ 
					// show PDO error message
					print_r($exception->getMessage()); 
				}
			}
		}
    }
	
    /**
     * Check login session is active
	 *
     */	
	public function checkLogin() 
	{
		// if admin session isn't set,
		if(!isset($_SESSION['admin_logged_in'])) 
		{
			// redirect to login page
			header('location: /admin'); 
			exit();
		}		
	}
	
    /**
     * Logout
	 *
     */	
	public function doLogout() 
	{
		// unset the session
		unset($_SESSION['admin_logged_in']);
		
		// redirect to login page
		header('location: /admin'); 
		exit();
	}
	
    /**
     * Create thumbnail from image
	 *
	 * @param $src (str) the image source path
	 * @param $dest (str) the image destination path
	 * @param $desired_width (int) the size of the thumbnail
     */		

	function createThumb($src, $dest, $desired_width) {

		// get the src image mime type 
		// so we know which to create from
		if(mime_content_type($src) == "image/png") 
		{
			$source_image = imagecreatefrompng($src);
		}
		else 
		{
			$source_image = imagecreatefromjpeg($src);
		}
		
		// get the width & height of original image
		$width = imagesx($source_image);
		$height = imagesy($source_image);
		
		// find the "desired height" of this thumbnail, relative to the desired width
		$desired_height = floor($height * ($desired_width / $width));
		
		// create a new, "virtual" image 
		$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
		
		// copy source image at a resized size
		imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
		
		// create the physical thumbnail image to its destination
		imagejpeg($virtual_image, $dest);
	}	
}