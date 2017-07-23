<?php

namespace ChronosDependency;

use Illuminate\Http\Request;
use Repo\Material;
use Repo\Equipment;
use Repo\Workforce;
use Repo\MaterialCost;
use Repo\EquipmentCost;
use Repo\WorkforceCost;
use Repo\Partitie;

use Auth;

trait SearchController
{
    /**
     * este controlador es mientras no hay un Framework MVC. ya que el deber ser
     * es que todo el Backend sea un API REST
     */


    public function materials(Request $request)
    {
    	$search = $request->search;

    	$materials = Material::where('companieId', Auth::user()->companieId)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            })
            ->orderBy('id', 'desc')
            ->take(10)//limitar o paginar
            ->get();

        foreach ($materials as $material) {
            $material->price = MaterialCost::where('materialId', $material->id)
                ->orderBy('id', 'desc')
                ->first()
                ->cost;
        }

    	return response()->json($materials);
    }

    public function equipments(Request $request)
    {
    	$search = $request->search;

    	$equipments = Equipment::where('companieId', Auth::user()->companieId)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            })
            ->orderBy('id', 'desc')
            ->take(10)//limitar o paginar
            ->get();

        foreach ($equipments as $equipment) {
            $equipment->price = EquipmentCost::where('equipmentId', $equipment->id)
                ->orderBy('id', 'desc')
                ->first()
                ->cost;
        }

    	return response()->json($equipments);
    }

    public function workforces(Request $request)
    {
        $search = $request->search;

        $workforces = Workforce::where('companieId', Auth::user()->companieId)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            })
            ->orderBy('id', 'desc')
            ->take(10)//limitar o paginar
            ->get();

        foreach ($workforces as $workforce) {
            $workforce->price = WorkforceCost::where('workforceId', $workforce->id)
                ->orderBy('id', 'desc')
                ->first()
                ->cost;
        }

        return response()->json($workforces);
    }

    public function partitie(Request $request)
    {
        $partitie = Partitie::where('companieId', Auth::user()->companieId)
            ->find($request->id);

        $partitie->materials = $partitie->materials()->get();
        
        foreach ($partitie->materials as $material) {
            $material->cost = $material->material()->first()->lastCost(); 
            $material->costId = $material->material()->first()->lastCostId();
        }

        $partitie->equipments = $partitie->equipments()->get();
        
        foreach ($partitie->equipments as $equipment) {
            $equipment->cost = $equipment->equipment()->first()->lastCost();
            $equipment->costId = $equipment->equipment()->first()->lastCostId();
            $equipment->depreciation = $equipment->equipment()->first()->depreciation; 
        }

        $partitie->workforces = $partitie->workforces()->get();
        
        foreach ($partitie->workforces as $workforce) {
            $workforce->cost = $workforce->workforce()->first()->lastCost(); 
            $workforce->costId = $workforce->workforce()->first()->lastCostId();
        }

        return response()->json($partitie);
    }

    public function partities(Request $request)
    {
        
        $search = $request->search;

        $partities = Partitie::where('companieId', Auth::user()->companieId)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            })
            ->orderBy('id', 'desc')
            ->take(10)//limitar o paginar
            ->get();

        return response()->json($partities);
    }
}
