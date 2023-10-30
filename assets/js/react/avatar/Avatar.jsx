import React, { useState } from 'react';
import AvatarChangeButton from './AvatarChangeButton';
import AvatarPicture from './AvatarPicture';

const Avatar = () => {
    const isMyself = window.globals.config.isMyself;

    // We will keep track of changeCount to enforce rerendering of profile picture
    const [changeCount, setChangeCount] = useState(0);

    const avatarWasChanged = () => {
        const newCount = changeCount + 1;
        setChangeCount(newCount);

        /*
        We will also find other profile pictures outside of react and update their src
        with the new changeCount to fool browser cache and reload the image
        */
        const miniAvatarObjectsElements = document.getElementsByClassName('js-profile-picture');
        for (let element of miniAvatarObjectsElements) {
            if (element.href !== undefined) {
                element.href = `${element.href}?${changeCount}`;
            } else {
                element.src = `${element.src}?${changeCount}`;
            }
        }
    }

    return (
        <>
            <AvatarPicture changeCount={changeCount} />

            { isMyself && <AvatarChangeButton onChange={avatarWasChanged} />}
        </>)
}

export default Avatar;
