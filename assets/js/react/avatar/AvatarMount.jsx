import React from 'react';
import ReactDom from 'react-dom';
import {parseGlobals} from '../../utils/globals'
import Avatar from './Avatar';

const mountId = 'react_mount';

parseGlobals(mountId);

ReactDom.render(<Avatar />, document.getElementById(mountId));

