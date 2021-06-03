import React, { useEffect } from 'react'


export default function Popup(props) {

    const {modalId, show} = props;
    useEffect(() => {
        window.$(`#${modalId}`).modal(show ? 'show' : 'hide')
    }, [modalId, show])


    return (
        <div className="modal fade" id={props.modalId} role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div className="modal-dialog modal-dialog-centered" role="document">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title" id="exampleModalLongTitle">{props.title}</h5>
                        <button type="button" className="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div className="modal-body">
                        {props.children}
                    </div>
                </div>
            </div>
        </div>
    )
}
