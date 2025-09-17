export default function userRole() {
  let role = JSON.parse(localStorage.getItem("user")).role;
  let status = JSON.parse(localStorage.getItem("user")).status;
  let otp = JSON.parse(localStorage.getItem("user")).otp;

  return {
    role: role,
    status: status,
    otp: otp
  };

}
