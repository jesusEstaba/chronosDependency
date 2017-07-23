<?php

namespace ChronosDependency;

use Illuminate\Http\Request;
use Repo\Project;
use Repo\ProjectPartitie;
use Repo\ProjectMaterial;
use Repo\ProjectEquipment;
use Repo\ProjectWorkforce;
use Repo\Modifier;
use Repo\Client;
use Auth;

use Cronos\model\Cost;
use Cronos\model\CostPartitie;

trait ProjectController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $projects = Project::where('companieId', Auth::user()->companieId)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('project.index', compact('projects', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $clients = Client::where('companieId', Auth::user()->companieId)->get();

        return view('project.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $projectId = Project::create([
            'name' => $request->name,
            'start' => date("Y-m-d"),
            'end' => date("Y-m-d"),
            'finish' => date("Y-m-d"),
            'companieId' => Auth::user()->companieId,
            'clientId' => $request->client,
            'stateId' => 1,
            'userId' => Auth::user()->id,
        ])->id;

        $modifiers = [
            [
                'name' => 'fcas',
                'amount' => $request->fcas,
                'type' => 1,
            ],
            [
                'name' => 'expenses',
                'amount' => $request->expenses,
                'type' => 1,
            ],
            [
                'name' => 'utility',
                'amount' => $request->utility,
                'type' => 1,
            ],
            [
                'name' => 'unexpected',
                'amount' => $request->unexpected,
                'type' => 1,
            ],
            [
                'name' => 'bonus',
                'amount' => $request->bonus,
                'type' => 1,
            ],
            [
                'name' => 'salary',
                'amount' => $request->salary,
                'type' => 2,
            ],
            [
                'name' => 'salaryBonus',
                'amount' => $request->salaryBonus,
                'type' => 2,
            ],
        ];

        foreach ($modifiers as $modifier) {
            Modifier::create(array_merge($modifier, [
                'projectId' => $projectId,
            ]));
        }

        if (count($request->partities)) {
            foreach ($request->partities as $partitie) {
                $partitieId = ProjectPartitie::create([
                    'yield' => $partitie['yield'],
                    'quantity' => $partitie['quantity'],
                    'projectId' => $projectId,
                    'partitieId' => $partitie['id'],
                    'userId' => Auth::user()->id,
                ])->id;

                foreach ($partitie['materials'] as $material) {
                    ProjectMaterial::create([
                        'partitieId' => $partitieId,
                        'materialId' => $material['materialId'],
                        'costId' => $material['costId'],
                    ]);
                }

                foreach ($partitie['equipments'] as $equipment) {
                    ProjectEquipment::create([
                        'partitieId' => $partitieId,
                        'equipmentId' => $equipment['equipmentId'],
                        'costId' => $equipment['costId'],
                    ]);
                }

                foreach ($partitie['workforces'] as $workforce) {
                    ProjectWorkforce::create([
                        'partitieId' => $partitieId,
                        'workforceId' => $workforce['workforceId'],
                        'costId' => $workforce['costId'],
                    ]);
                }
            }
        }

        session()->flash('success', 'Proyecto Creado.');

        return response()->json(['status'=>'redirect']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::where('companieId', Auth::user()->companieId)->find($id);

        $projectModifiers = Modifier::where('projectId', $id)->get();

        $modifiers = [];

        foreach ($projectModifiers as $modifier) {
            $modifiers[$modifier->name] = $modifier->amount;
        }

        $calculator = new CostPartitie($modifiers);

        return view('project.show', compact('project', 'projectModifiers', 'calculator'));
    }

    public function pdf($id)
    {
        $project = Project::where('companieId', Auth::user()->companieId)->find($id);

        $projectModifiers = Modifier::where('projectId', $id)->get();

        $modifiers = [];

        foreach ($projectModifiers as $modifier) {
            $modifiers[$modifier->name] = $modifier->amount;
        }

        $calculator = new CostPartitie($modifiers);
        
        $pdf = \PDF::loadView('pdf.partitie', compact('project', 'calculator'));
        
        return $pdf->stream();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
