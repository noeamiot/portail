<?php

namespace App\Http\Controllers;

use App\Models\Asso;
use App\Http\Requests\AssoRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AssoController extends Controller
{
	public function __construct() {
		// $this->middleware('auth:api', ['except' => ['index', 'show']]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		$assos = Asso::get();
		return response()->json($assos, 200);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(AssoRequest $request) {
		$asso = Asso::create($request->input());
		if ($asso)
			return response()->json($asso, 201);
		else
			return response()->json(["message" => "Impossible de créer l'association"], 500);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		$asso = Asso::find($id);
		if($asso)
			return response()->json($asso,200);
		return response()->json(['message'=>'L\'asso demandée n\'a pas pu être trouvée'], 404);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(AssoRequest $request, $id)
	{
		$asso = Asso::find($id);
		if($asso){
			$ok = $asso->update($request->input());
			if($ok)
				return response()->json($asso,201);
			return response()->json(['message'=>'L\'association n\'a pas pu être modifiée'],500);
		}
		return response()->json(['message'=>'L\'association demandée n\'a pas été trouvée'],404);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$asso = Asso::find($id);
		if($asso){
			$ok = $asso->destroy();
			if($ok)
				return response()->json(['message'=>'L\'assocition a bien été supprimée'],200);
			return response()->json(['message'=>'L\'association n\'a pas pu être supprimée'],500);
		}
		return response()->json(['message'=>'L\'association demandée n\'a pas pu être trouvée'],404);
	}
}
