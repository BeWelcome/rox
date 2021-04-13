import React from 'react';
import ReactDom from 'react-dom';
import AvatarChangeButton from './AvatarChangeButton'
import {parseGlobals} from '../../utils/globals'

const mountId = 'react_mount';

parseGlobals(mountId);

ReactDom.render(<AvatarChangeButton />, document.getElementById(mountId));