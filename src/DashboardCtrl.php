<?php

namespace Cronos\Http\Controllers;

use Illuminate\Http\Request;
use Repo\Partitie;
use Repo\Client;
use Repo\Project;
use Auth;

trait DashboardController
{
    public function index()
    {
    	$projects = Project::where('companieId', Auth::user()->companieId)
            ->get();
    	$numOfProjects = Project::where('companieId', Auth::user()->companieId)
            ->count();
    	$numOfClients = Client::where('companieId', Auth::user()->companieId)
            ->count();
    	$numOfPartities = Partitie::where('companieId', Auth::user()->companieId)
            ->count();

    	return view('dashboard', compact(
    		'numOfProjects',
			'numOfClients',
			'numOfPartities',
            'projects'
    	));
    }
}
