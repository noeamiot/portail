<?php

namespace App\Models;

use Cog\Contracts\Ownership\CanBeOwner;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\HasMembers;
use App\Traits\Model\HasStages;
use App\Interfaces\Model\CanHaveContacts;
use App\Interfaces\Model\CanHaveEvents;
use App\Interfaces\Model\CanHaveCalendars;
use App\Interfaces\Model\CanHaveArticles;

class Asso extends Model implements CanBeOwner, CanHaveContacts, CanHaveCalendars, CanHaveEvents, CanHaveArticles
{
	use HasStages, HasMembers, SoftDeletes {
		HasMembers::members as membersAndFollowers;
		HasMembers::currentMembers as currentMembersAndFollowers;
		HasMembers::joiners as protected joinersFromHasMembers;
		HasMembers::currentJoiners as currentJoinersFromHasMembers;
		HasMembers::getUserRoles as getUsersRolesInThisAssociation;
	}

	protected $fillable = [
		'name', 'shortname', 'login', 'description', 'type_asso_id', 'parent_id',
	];

	protected $hidden = [
		'type_asso_id', 'parent_id',
	];

	protected $with = [
		'type', 'parent',
	];

	protected $optional = [
		'children',
	];

	protected $must = [
		'name', 'shortname',
	]; // Children dans le cas où on affiche en mode étagé

	protected $selection = [
		'stage' => null,
		'stages' => null,
	];

	protected $roleRelationTable = 'assos_members';

	public static function boot() {
        static::created(function ($model) {
			// On crée automatiquement des moyens de contacts !
			Contact::create([
				'name' => 'Adresse email',
				'value' => $model->login.'@assos.utc.fr',
				'contact_type_id' => ContactType::where('name', 'Adresse email')->first()->id,
				'visibility_id' => Visibility::findByType('public')->id,
			])->changeOwnerTo($model)->save();

			Contact::create([
				'name' => 'Site Web',
				'value' => 'https://assos.utc.fr/'.$model->login.'/',
				'contact_type_id' => ContactType::where('name', 'Url')->first()->id,
				'visibility_id' => Visibility::findByType('public')->id,
			])->changeOwnerTo($model)->save();
        });
    }

	public function scopeFindByLogin($query, string $login) {
		return $query->where('login', $login)->first();
	}

	public function type() {
		return $this->belongsTo(AssoType::class, 'type_asso_id');
	}

	public function rooms() {
		return $this->hasMany(Room::class);
	}

	public function reservations() {
		return $this->hasMany(Reservation::class);
	}

	public function parent() {
	    return $this->hasOne(Asso::class, 'id', 'parent_id');
    }

	public function children() {
		return $this->hasMany(Asso::class, 'parent_id', 'id');
    }

	public function members() {
		return $this->membersAndFollowers()->wherePivot('role_id', '!=', null);
	}

	public function currentMembers() {
		return $this->currentMembersAndFollowers()->wherePivot('role_id', '!=', null);
	}

	public function joiners() {
		return $this->joinersFromHasMembers()->wherePivot('role_id', '!=', null);
	}

	public function currentJoiners() {
		return $this->currentJoinersFromHasMembers()->wherePivot('role_id', '!=', null);
	}

	public function followers() {
		return $this->membersAndFollowers()->wherePivot('role_id', null);
	}

	public function currentFollowers() {
		return $this->currentMembersAndFollowers()->wherePivot('role_id', null);
	}

	public function getUserRoles(int $user_id = null, int $semester_id = null) {
		$parent_id = $this->parent_id;
		$roles = $this->getUsersRolesInThisAssociation($user_id, $semester_id);

		while ($parent_id) {
			$asso = static::find($parent_id);

			foreach ($asso->getUsersRolesInThisAssociation($user_id, $semester_id) as $role) {
				$roles->push($role);

				$roles = $roles->merge($role->allChildren());
				$role->makeHidden('children');
			}

			$parent_id = $asso->parent_id;
		}

		return $roles->unique('id');
	}

	public function getLastUserWithRole($role) {
		return $this->members()->wherePivot('role_id', Role::getRole($role)->id)->orderBy('semester_id', 'DESC')->first();
	}

	public function contacts() {
		return $this->morphMany(Contact::class, 'owned_by');
	}

	public function isContactAccessibleBy(int $user_id): bool {
		return $this->currentMembers()->wherePivot('user_id', $user_id)->exists();
	}

	public function isContactManageableBy(int $user_id): bool {
		return $this->hasOnePermission('asso_contact', [
			'user_id' => $user_id,
		]);
	}

    public function calendars() {
    	return $this->morphMany(Calendar::class, 'owned_by');
    }

	public function isCalendarAccessibleBy(int $user_id): bool {
		return $this->currentMembers()->wherePivot('user_id', $user_id)->exists();
	}

	public function isCalendarManageableBy(int $user_id): bool {
		return $this->hasOnePermission('asso_calendar', [
			'user_id' => $user_id,
		]);
	}

    public function events() {
    	return $this->morphMany(Events::class, 'owned_by');
    }

	public function isEventAccessibleBy(int $user_id): bool {
		return $this->currentMembers()->wherePivot('user_id', $user_id)->exists();
	}

	public function isEventManageableBy(int $user_id): bool {
		return $this->hasOnePermission('asso_event', [
			'user_id' => $user_id,
		]);
	}

    public function articles() {
    	return $this->morphMany(Article::class, 'owned_by');
    }

    public function collaboratedArticles() {
    	return $this->morphToMany(Article::class, 'collaborator', 'articles_collaborators');
    }

	public function isArticleAccessibleBy(int $user_id): bool {
		return $this->currentMembers()->wherePivot('user_id', $user_id)->exists();
	}

	public function isArticleManageableBy(int $user_id): bool {
		return $this->hasOnePermission('asso_article', [
			'user_id' => $user_id,
		]);
	}
}
