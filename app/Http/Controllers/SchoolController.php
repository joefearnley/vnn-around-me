<?php namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class SchoolController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 * 
	 * @Get('school/find')
	 */
	public function index(Request $request)
	{
		$latitude = $request->input('latitude');
		$longitude = $request->input('longitude');

		// do the math to find the closest one
		// and return that School's data


	}

}
