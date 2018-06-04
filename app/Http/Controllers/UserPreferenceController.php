<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\UserPreference;

class UserPreferenceController extends Controller
{
    public function __construct() {
		$this->middleware(
			\Scopes::matchOne(
				['user-get-roles-users']
			),
			['only' => ['index', 'show']]
		);
		$this->middleware(
			\Scopes::matchOne(
				['user-set-roles-users']
			),
			['only' => ['store', 'update']]
		);
		$this->middleware(
			\Scopes::matchOne(
				['user-manage-roles-users']
			),
			['only' => ['destroy']]
		);
    }

	protected function getUser(Request $request, int $user_id = null) {
        if (\Scopes::isClientToken($request))
            $user = User::find($user_id ?? null);
        else {
            $user = \Auth::user();

            if (!is_null($user_id) && $user->id !== $user_id)
                abort(403, 'Il ne vous est pas autorisé d\'accéder aux rôles des autres utilisateurs');
        }

		if ($user)
			return $user;
		else
			abort(404, "Utilisateur non trouvé");
	}

    protected function getPreferences(Request $request, $user) {
        $choices = $this->getChoices($request, ['global', 'asso', 'client']);
        $token = $request->user() ? $request->user()->token() : $request->token();
        $client = $token->client;

        if (in_array('asso', $choices))
            $choices[array_search('asso', $choices)] = 'asso-'.$client->asso_id;

        if (in_array('client', $choices))
            $choices[array_search('client', $choices)] = 'client-'.$client->id;

        return $user->preferences()->whereIn('only_for', $choices);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, int $user_id = null) {
		$user = $this->getUser($request, $user_id);
        $groups = $this->getPreferences($request, $user)->get()->groupBy('only_for');
        $array = [];

        foreach ($groups as $only_for => $preferences) {
            $array[$only_for] = [];

            foreach ($preferences as $preference)
                $array[$only_for] = array_merge($array[$only_for], $preference->toArray());
        }

        if (count($array) === 1)
            return response()->json(array_values($array)[0]);
        else
            return response()->json($array);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, int $user_id = null) {
		$user = $this->getUser($request, $user_id);
        $inputs = $request->input();

        // On ajoute l'id associé
        if ($request->input('only_for') === 'asso') {
            $token = $request->user() ? $request->user()->token() : $request->token();
            $inputs['only_for'] .= '_'.$token->client->asso_id;
        }
        else if ($request->input('only_for') === 'client') {
            $token = $request->user() ? $request->user()->token() : $request->token();
            $inputs['only_for'] .= '_'.$token->client->id;
        }
        else if ($request->input('only_for', 'global') !== 'global')
            abort(400, 'only_for peut seulement être asso, client ou global');

		if (\Scopes::isUserToken($request)) {
			$preference = UserPreference::create(array_merge(
				$inputs,
				['user_id' => $user->id]
			));

			return response()->json($preference, 201);
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $user_id, $key = null) {
        if (is_null($key))
            list($user_id, $key) = [$key, $user_id];

        $user = $this->getUser($request, $user_id);
		$preference = $this->getPreferences($request, $user)->key($key);

		if ($preference)
			return response()->json($preference->toArray());
		else
			abort(404, 'Cette personne ne possède pas cette préférence');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user_id, $key = null) {
        if (is_null($key))
            list($user_id, $key) = [$key, $user_id];

		$user = $this->getUser($request, $user_id);

		if (\Scopes::isUserToken($request)) {
            try {
                $preference = $this->getPreferences($request, $user)->key($key);
                $preference->value = $request->input('value', $preference->value);

                if ($preference->update())
                    return response()->json($preference);
                else
                    abort(503, 'Erreur lors de la modification');
            }
            catch (PortailException $e) {
                abort(404, 'Cette personne ne possède pas ce préférence, ou il ne peut être modifié');
            }
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $user_id, $key = null) {
        if (is_null($key))
            list($user_id, $key) = [$key, $user_id];

		$user = $this->getUser($request, $user_id);

		if (\Scopes::isUserToken($request)) {
            $preference = $this->getPreferences($request, $user)->key($key);

            try {

				if ($preference->delete())
                    abort(204);
				else
					abort(503, 'Erreur lors de la suppression');
            }
            catch (PortailException $e) {
                abort(404, 'Cette personne ne possède pas ce détail, ou il ne peut être supprimé');
            }
		}
    }
}
