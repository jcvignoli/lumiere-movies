import React from 'react';
import { registerPlugin } from '@wordpress/plugins'; 
import Lum_Sidebar_Options from './options.js';
import jsonData from './block.json';
import './index.css';

registerPlugin( jsonData.name, {
	render() {
		return(<Lum_Sidebar_Options />);
	}
} );

