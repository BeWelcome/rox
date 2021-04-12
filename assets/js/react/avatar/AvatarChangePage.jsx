import React from 'react';
import ReactDom from 'react-dom';
import AvatarChangeButton from './AvatarChangeButton'
import {parseGlobals} from '../../utils/globals'

parseGlobals('main');

ReactDom.render(<AvatarChangeButton />, document.getElementById('main'));