export default function authHeader() {
  if (window.localStorage.getItem("authenticated") === "false") {
    window.location = '/'
  }
  let user = JSON.parse(localStorage.getItem('user'));

  if (user && user.accessToken) {
    return {
      headers: {
        Authorization: 'Bearer ' + user.accessToken,
        Accept: 'application/json'
      }
    };      // for Node.js Express back-end
  } else {
    window.location = '/'
    return {};
  }
}
