function viewAllCars() 
{
	// redirect to add new car
	window.location.href = "/admin/cars/all";
}

function updateCars() 
{
	// notify admin we are updating the cars
	document.getElementById("update_cars_button").innerHTML = "<img style='vertical-align: middle' src='/assets/images/ajax-loader.gif'> Updating cars, please wait ...";
 
	// update cars
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) 
		{
		   // reload the page with the updated information
		   window.location.href = "/admin/cars/all";
		}
	};
	xhttp.open("GET", "/admin/cars/update", true);
	xhttp.send();
}

function addNewCar() 
{
	// redirect to add new car
	window.location.href = "/admin/cars/add";
}

function doLogout() 
{
	// redirect to logout
	window.location.href = "/admin/logout";
}