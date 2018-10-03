import React from 'react';
import { connect } from 'react-redux';

import { Card, CardBody, CardTitle, CardSubtitle, CardFooter, Button } from 'reactstrap';
import AspectRatio from 'react-aspect-ratio';
import { findIndex } from 'lodash';

import Img from '../Image';

class Member extends React.Component {
	render() {
		return (
			<Card className="m-2 p-0" style={{ width: 225 }}>
				<AspectRatio ratio="1" style={{ height: 200 }} className="d-flex justify-content-center mt-2">
					<Img image={ this.props.image } className="img-thumbnail" style={{ height: '100%' }} unloader={(
						<div className="d-flex justify-content-center align-items-center">
							<i className="fa fa-9x fa-user-alt"></i>
						</div>
					)} />
				</AspectRatio>
				<CardBody>
					<CardTitle>{ this.props.title }</CardTitle>
					<CardSubtitle>{ this.props.description }</CardSubtitle>
				</CardBody>
				<CardFooter>
					{ this.props.footer }
				</CardFooter>
			</Card>
		)
	}
}

export default Member;
