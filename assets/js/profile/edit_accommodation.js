import { initializeAccommodationWidget } from "../accommodation_widget";

const hostingInterest = document.getElementById('accommodation_form_hosting_interest')

initializeAccommodationWidget()

// now register on change handler for the radio buttons to change state when the user clicks

const updateAccommodation = async (e) => {
    const accommodation = document.querySelector('input[name="accommodation_form[accommodation]"]:checked').value;
    const newAccommodationValue = {
        accommodation: accommodation,
        hostingInterest: +hostingInterest.value
    }

    await fetch('/members/update/accommodation',  {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(newAccommodationValue)
    }).then((response) => {
    })
}

const updateOffers = async (e) => {
    const newOffers = {
        dinner: offers[0].checked,
        tour: offers[1].checked,
        accessible: offers[2].checked
    }

    await fetch('/members/update/offers',  {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(newOffers)
    }).then((response) => {
    })
}

const updateRestrictions = async (e) => {
    const newRestrictions = {
        noAlcohol: restrictions[0].checked,
        noSmoking: restrictions[1].checked,
        noDrugs: restrictions[2].checked
    }

    await fetch('/members/update/restrictions',  {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(newRestrictions)
    }).then((response) => {
    })
}

const accommodationRadiobuttons = document.querySelectorAll(('.js-accommodation'))
accommodationRadiobuttons.forEach( (radio) => {
    radio.addEventListener("change", updateAccommodation)
})

hostingInterest.addEventListener("change", updateAccommodation)

const offers = document.querySelectorAll('[data-offer]')
offers.forEach( (offer) => {
    offer.addEventListener("change", updateOffers)
})

const restrictions = document.querySelectorAll('[data-restrictions]')
restrictions.forEach( (restriction) => {
    restriction.addEventListener("change", updateRestrictions)
})
