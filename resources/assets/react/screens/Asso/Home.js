/**
 * Affichage principal d'une association
 *
 * @author Alexandre Brasseur <abrasseur.pro@gmail.com>
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 * @author Natan Danous <natous.danous@hotmail.fr>
 *
 * @copyright Copyright (c) 2018, SiMDE-UTC
 * @license GNU GPL-3.0
 */

import React from 'react';
import { connect } from 'react-redux';
import AspectRatio from 'react-aspect-ratio';
import { Button } from 'reactstrap';
import ReactMarkdown from 'react-markdown';

import actions from '../../redux/actions';

import ContactList from '../../components/Contact/List';
import Img from '../../components/Image';

@connect((store, props) => ({
	config: store.config,
	isAuthenticated: store.isFetched('user'),
	contacts: store.getData(['assos', props.asso.id, 'contacts']),
	contactsFailed: store.hasFailed(['assos', props.asso.id, 'contacts']),
	contactsFetched: store.isFetched(['assos', props.asso.id, 'contacts']),
	roles: store.getData(['assos', props.asso.id, 'roles']),
}))
class AssoHomeScreen extends React.Component {
	componentWillMount() {
		const {
			asso: { id },
			contactsFetched,
		} = this.props;

		if (id && !contactsFetched) {
			this.loadAssosData(id);
		}
	}

	componentDidUpdate({ asso }) {
		const {
			asso: { id },
		} = this.props;

		if (asso.id !== id) {
			this.loadAssosData(id);
		}
	}

	getFollowButton(isFollowing, isMember) {
		const { follow, unfollow } = this.props;

		if (isFollowing && !isMember) {
			return (
				<Button className="m-1 btn btn-sm font-weight-bold" color="danger" outline onClick={unfollow}>
					Ne plus suivre
				</Button>
			);
		}

		if (isMember) {
			return null;
		}

		return (
			<Button className="m-1 btn btn-sm font-weight-bold" color="primary" outline onClick={follow}>
				Suivre
			</Button>
		);
	}

	getMemberButton(isMember, isFollowing, isWaiting) {
		const { leave, join } = this.props;

		if (isMember) {
			if (isWaiting) {
				return (
					<Button
						className="m-1 btn btn-sm font-weight-bold"
						color="warning"
						outline
						onClick={() => {
							leave(true);
						}}
					>
						En attente...
					</Button>
				);
			}

			return (
				<Button
					className="m-1 btn btn-sm font-weight-bold"
					color="danger"
					outline
					onClick={() => {
						leave(false);
					}}
				>
					Quitter
				</Button>
			);
		}

		if (isFollowing) {
			return (
				<Button className="m-1 btn btn-sm" outline disabled>
					Rejoindre
				</Button>
			);
		}

		return (
			<Button className="m-1 btn btn-sm" color="primary" outline onClick={join}>
				Rejoindre
			</Button>
		);
	}

	loadAssosData(id) {
		const { dispatch } = this.props;

		dispatch(actions.assos(id).contacts.all());
	}

	render() {
		const {
			asso,
			config,
			isAuthenticated,
			userIsFollowing,
			userIsMember,
			userIsWaiting,
			contacts,
			contactsFailed,
		} = this.props;
		config.title = asso.shortname;

		let color = `color-${asso.login}`;

		if (asso.parent) color += ` color-${asso.parent.login}`;

		return (
			<div className="container">
				{asso ? (
					<div className="row">
						<div className="col-md-2 mt-3 px-1 d-flex flex-md-column">
							<AspectRatio className="mb-2" ratio="1">
								<Img image={asso.image} style={{ width: '100%' }} />
							</AspectRatio>
							{isAuthenticated && this.getFollowButton(userIsFollowing, userIsMember)}
							{isAuthenticated &&
								this.getMemberButton(userIsMember, userIsFollowing, userIsWaiting)}
						</div>
						<div className="col-md-8" style={{ whiteSpace: 'pre-line' }}>
							<h1 className={`title ${color}`} style={{ fontWeight: 'bold' }}>
								{asso.shortname} <small className="text-muted h4" style={{ fontStyle: 'italic' }}>{asso.name}</small>
							</h1>
							<span className="mt-4">{asso.type && asso.type.description}</span>
							<ReactMarkdown className="my-3 text-justify" source={asso.description} />
							<ContactList className="mt-4" contacts={contacts} authorized={!contactsFailed} />
						</div>
					</div>
				) : null}
			</div>
		);
	}
}

export default AssoHomeScreen;
