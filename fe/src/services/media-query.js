export default function isMobile() {
    let isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;
    if (isMobile) {
        return true;
    } else { 
       return false;
    }

}
