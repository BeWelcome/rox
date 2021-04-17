import React, { useState } from 'react';
import AvatarChangeButton from './AvatarChangeButton';
import AvatarPicture from './AvatarPicture';

const Avatar = () => {
    const isMyself = window.globals.config.isMyself;

    // We will keep track of changeCount to enforce rerendering of profile picture
    const [changeCount, setChangeCount] = useState(0);

    const avatarWasChanged = () => {
        setChangeCount(changeCount + 1);
    }

    return (
        <>
            <AvatarPicture changeCount={changeCount} />

            { isMyself && <AvatarChangeButton onChange={avatarWasChanged} />}
        </>)
}

export default Avatar;