import axios from "axios";
import authHeader from "./auth-header";

// const API_URL = 'https://apiv1.clinicPluszimbabwe.com/api/';
const API_URL = 'http://localhost:8000/api/';
class AuthService {
  login(user) {
    return axios
      .post(API_URL + "login", {
        email: user[0],
        password: user[1],
      })
      .then((response) => {
        if (response.data.accessToken) {
          localStorage.setItem("user", JSON.stringify(response.data));
        }

        return response.data;
      });
  }

  logout() {
    return axios
      .post(API_URL + "logout", {
        headers: authHeader(),
      })
      .then((response) => {
        console.log(response);
        if (response) {
          localStorage.removeItem("user");
        }
      });
  }

  register(user) {
    return axios.post(API_URL + "register", {
      name: user[0],
      email: user[1],
      password: user[2],
      c_password: user[3],
    });
  }

  forgot(user) {
    return axios.post(API_URL + "password/email", {
      email: user[0],
    });
  }

  reset(user) {
    return axios.post(API_URL + "password/reset", {
      email: user[0],
      token: user[1],
      password: user[2],
      password_confirmation: user[3],
    });
  }
}

export default new AuthService();
