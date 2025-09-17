export default function authHeader() {
  let user = JSON.parse(localStorage.getItem('user'));

  if (user && user.accessToken) {
    return {
      headers: {
        Authorization: 'Bearer ' + user.accessToken,
        Accept: 'application/json'
      }
    };      // for Node.js Express back-end
  } else {
    return {};
  }
}
