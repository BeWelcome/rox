import React from 'react';
import {getText} from '../../utils/texts';

const AvatarPicture = (props) => {
    const config = window.globals.config;
    const basePictureUrl = config.avatarUrl;
    const avatarOriginalUrl = `${basePictureUrl}/original`;
    const pictureUrl = `/members/avatar/${config.username}/500`;
    const pictureTitle = getText('profile.picture.title');

    return (
        <div className="u-w-full u-relative u-pb-[100%]">
            <div className="u-absolute u-left-0 u-top-0 u-w-full u-h-full">
                <a href={avatarOriginalUrl} title={pictureTitle} className="js-profile-picture" data-toggle="lightbox" data-type="image">
                    <img className="u-rounded-8 u-w-full u-h-full u-object-cover js-profile-picture"
                         src={pictureUrl} alt={pictureTitle}/>
                </a>
            </div>
        </div>
    )
}

export default AvatarPicture;
