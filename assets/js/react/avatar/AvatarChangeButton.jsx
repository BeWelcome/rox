import React from 'react';
import {uploadTemporaryAvatar} from '../../api/avatar'
import {getText} from '../../utils/texts';
import {alertError, alertSuccess} from '../../utils/alerts';

const BLINKING_BTN_COLORS = ['#0083f3', '#53a8f1'];

const AvatarChangeButton = (props) => {
    const [uploading, setUploading] = React.useState(false);
    const [btnColorIx, setBtnColorIx] = React.useState(0);
    const inputFile = React.useRef(null)
    // Timeout closure's state doesnt get updated. That's why we are using ref so that the closeru has up to data info
    const animationState = React.useRef();
    animationState.current = {btnColorIx, uploading};

    const animateBtn = () => {
        setBtnColorIx(animationState.current ? 0 : 1);
        setTimeout(animateBtn, 700);
    }

    const onChangeFile = async (event) => {
        event.stopPropagation();
        event.preventDefault();
        var file = event.target.files[0];

        setUploading(true);
        animateBtn();

        if (file) {
            const result = await uploadTemporaryAvatar(file);

            setUploading(false);

            if (result?.status && result.status >= 200 && result.status < 300) {
                props.onChange();
                alertSuccess(getText('profile.change.avatar.success'));
            } else if (result?.status === 413) {
                alertError(getText('profile.change.avatar.fail.file.to.big'));
            } else {
                alertError(getText('profile.change.avatar.fail'));
            }
        }
        inputFile.current.value = null;
    }

    const onButtonClick = () => {
       inputFile.current.click();
    };

    const btnStyle = {
        cursor: uploading ? 'wait' : 'pointer',
        transition: 'all .7s',
        backgroundColor: BLINKING_BTN_COLORS[btnColorIx],
    }

    return <>
        <input type='file' id='file' ref={inputFile} style={{display: 'none'}} onChange={onChangeFile} />
        <button type="button" onClick={onButtonClick} className="btn btn-info btn-block" disabled={uploading} style={btnStyle} >{getText('profile.change.avatar')}</button>
    </>
}

export default AvatarChangeButton;
