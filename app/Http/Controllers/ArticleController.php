<?php

namespace App\Http\Controllers;

use App\Facades\Scopes;
use App\Models\Visibility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Traits\HasVisibility;
use Psy\Util\Json;

/**
 * @resource Article
 *
 * Les articles écrits et postés par les associations
 */
class ArticleController extends Controller
{
	use HasVisibility; //Utilisation du Trait HasVisibility

	/**
	 * Scopes Article
	 *
	 * Les Scopes requis pour manipuler les Articles
	 */
	public function __construct() {
		$this->middleware(
			\Scopes::matchOne(
				['user-get-articles-followed-now', 'user-get-articles-done-now'],
				['client-get-articles-public']
			),
			['only' => ['index', 'show']]
		);
		$this->middleware(
			\Scopes::matchOne(
				['user-manage-articles']
			),
			['only' => ['store', 'update', 'destroy']]
		);
	}

	/**
	 * List Articles
	 *
	 * Retourne la liste des articles. ?all pour voir ceux en plus des assos suivies, ?notRemoved pour uniquement cacher les articles non visibles
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse
	 */
	public function index(Request $request) : JsonResponse
	{
		if ($request->user()){
			if (isset($request['all'])){
				$articles = Article::all();
			}
			else{
				$articles = Article::whereHas('collaborators', function ($query) use ($request){
					$query->whereIn('asso_id', array_merge(
						$request->user()->currentAssos()->pluck('assos.id')->toArray()
					));
				})->get();
			}
		}
		else{
			$articles = Scopes::has($request, 'client-get-articles') && isset($request['all']) ? Article::all() : Article::where('visibility_id', Visibility::where('type', 'public')->first()->id)->get();
		}
		return response()->json($request->user() ? $this->hide($articles, !isset($request['notRemoved'])) : $articles,200);
	}

	/**
	 * Create Article
	 *
	 * Créer un article
	 * @param ArticleRequest $request
	 * @return JsonResponse
	 */
	public function store(ArticleRequest $request) : JsonResponse
	{
		$article = Article::create($request->input());

		if ($article)
			return response()->json($article, 201);
		else
			return response()->json(['message' => 'Impossible de créer l\'article'], 500);
	}

	/**
	 * Show Article
	 *
	 * Affiche l'article s'il existe et si l'utilisateur peut le voir.
	 * @param Request $request
	 * @param  int $id
	 * @return JsonResponse
	 */
	public function show(Request $request, int $id) : JsonResponse
	{
		$article = Article::find($id);
		if (!isset($article))
			return response()->json(['message' => 'Impossible de trouver l\'article demandé'], 404);
		else
			return response()->json(Scopes::has($request, 'client-get-articles') ? $article : $this->hide($article, false),200);
	}

	/**
	 * Update Article
	 *
	 * Met à jour l'article s'il existe
	 * @param  \Illuminate\Http\ArticleRequest  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(ArticleRequest $request, $id)
	{
		$article = Article::find($id);

		if ($article) {
			if ($article->update($request->input()))
				return response()->json($article, 201);
			else
				return response()->json(['message' => 'impossible de modifier l\'article'], 500);
		}

		return response()->json(['message' => 'L\'article demandé n\'a pas été trouvé'], 404);
	}

	/**
	 * Delete Article
	 *
	 * Supprime l'article s'il existe
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$article = Article::find($id);

		if ($article) {
			if ($article->delete())
				return response()->json(['message' => 'L\'article a bien été supprimé'], 200);
			else
				return response()->json(['message' => 'Une erreur est survenue'],500);
		}

		return response()->json(['message' => 'L\'article demandé n\'a pas été trouvé'], 404);
	}
}
