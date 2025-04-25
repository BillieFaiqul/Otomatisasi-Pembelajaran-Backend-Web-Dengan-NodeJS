const mongoose = require("mongoose");
const request = require("supertest");
const app = require("../../app");
const bcrypt = require("bcryptjs");

require("dotenv").config();
mongoose.set("strictQuery", true);

const options = {
  showPrefix: false,
  showMatcherMessage: true,
  showStack: true,
};

const user = {
  name: "testnameforuser",
  username: "testusernameforuser",
  email: "testemailfor@user.com",
  password: "testpasswordforuser",
  confirmPassword: "testpasswordforuser",
};

const userUpdate = {
  name: "testnameforuserupdated",
  username: "testusernameforuserupdated",
  email: "testemailfor@userupdated.com",
  password: "testpasswordforuserupdated",
  confirmPassword: "testpasswordforuserupdated",
};

let token;
let returnedUser;

beforeAll(async () => {
  await connectDB().then(
    async () => {
      user.password = bcrypt.hashSync(user.password, bcrypt.genSaltSync(10));
      await mongoose.connection.collection("users").insertOne(user);
      user.password = "testpasswordforuser";
      console.log("Database connected successfully");
    },
    (err) => {
      console.log("There is problem while connecting database " + err);
    }
  );
});

describe("Testing PATCH /api/v1/profile", () => {
  it("should login a user with username", async () => {
    const res = await request(app).post("/api/v1/login").send({
      username: user.username,
      password: user.password,
    });
    expect(
      res.statusCode,
      `The status code should be 200, but it is "${res.statusCode}", change the status code in the function that handles the POST /api/v1/login route`,
      options
    ).toBe(200);
    expect(
      res.body,
      `The response body should have a property called "token", but it doesn't, change the response body in the function that handles the POST /api/v1/login route`,
      options
    ).toHaveProperty("token");
    token = res.body.token;
  });

  it("should update the user data", async () => {
    const res = await request(app)
      .patch("/api/v1/profile")
      .set("Authorization", `Bearer ${token}`)
      .send({
        name: userUpdate.name,
        username: userUpdate.username,
        email: userUpdate.email,
      });
    expect(
      res.statusCode,
      `The status code should be 200, but it is "${res.statusCode}", change the status code in the function that handles the PATCH /api/v1/profile route`,
      options
    ).toBe(200);
    expect(
      res.body,
      `The response body should have a property called "user", but it doesn't, change the response body in the function that handles the PATCH /api/v1/profile route`,
      options
    ).toHaveProperty("user");
    returnedUser = res.body.user;
    expect(
      {
        name: returnedUser.name,
        username: returnedUser.username,
        email: returnedUser.email,
      },
      `The response body should have a property called "user" with the correct values, but it doesn't, change the response body in the function that handles the PATCH /api/v1/profile route`,
      options
    ).toEqual({
      name: userUpdate.name,
      username: userUpdate.username,
      email: userUpdate.email,
    });
  });

  it("should update the user password", async () => {
    const res = await request(app)
      .patch("/api/v1/profile/password")
      .set("Authorization", `Bearer ${token}`)
      .send({
        currentPassword: user.password,
        newPassword: userUpdate.password,
        confirmNewPassword: userUpdate.confirmPassword,
      });
    expect(
      res.statusCode,
      `The status code should be 200, but it is "${res.statusCode}", change the status code in the function that handles the PATCH /api/v1/profile/password route`,
      options
    ).toBe(200);
    expect(
      res.body,
      `The response body should have a property called "message", but it doesn't, change the response body in the function that handles the PATCH /api/v1/profile/password route`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response body should have a property called "message" with the correct value, but it doesn't, it should be "Password Updated Successfully" change the response body in the function that handles the PATCH /api/v1/profile/password route`,
      options
    ).toBe("Password Updated Successfully");
  });

  it("should not update the user password if the current password is wrong", async () => {
    const res = await request(app)
      .patch("/api/v1/profile/password")
      .set("Authorization", `Bearer ${token}`)
      .send({
        currentPassword: user.password,
        newPassword: userUpdate.password,
        confirmNewPassword: userUpdate.confirmPassword,
      });
    expect(
      res.statusCode,
      `The status code should be 400, but it is "${res.statusCode}", change the status code in the function that handles the PATCH /api/v1/profile/password route`,
      options
    ).toBe(400);
    expect(
      res.body.message,
      `The response body should have a property called "message" with the correct value, but it doesn't, it should be "Incorrect Password" change the response body in the function that handles the PATCH /api/v1/profile/password route`,
      options
    ).toBe("Incorrect Password");
  });

  it("should not update the user password if the new password and confirm new password are not the same", async () => {
    const res = await request(app)
      .patch("/api/v1/profile/password")
      .set("Authorization", `Bearer ${token}`)
      .send({
        currentPassword: userUpdate.password,
        newPassword: userUpdate.password,
        confirmNewPassword: "testpasswordforuserupdated1",
      });
    expect(
      res.statusCode,
      `The status code should be 400, but it is "${res.statusCode}", change the status code in the function that handles the PATCH /api/v1/profile/password route`,
      options
    ).toBe(400);
    expect(
      res.body.message,
      `The response body should have a property called "message" with the correct value, but it doesn't, it should be "New password do not match" change the response body in the function that handles the PATCH /api/v1/profile/password route`,
      options
    ).toBe("New password do not match");
  });

  it("should not update the user password if the new password is less than 8 characters", async () => {
    const res = await request(app)
      .patch("/api/v1/profile/password")
      .set("Authorization", `Bearer ${token}`)
      .send({
        currentPassword: userUpdate.password,
        newPassword: "test",
        confirmNewPassword: "test",
      });
    expect(
      res.statusCode,
      `The status code should be 400, but it is "${res.statusCode}", change the status code in the function that handles the PATCH /api/v1/profile/password route`,
      options
    ).toBe(400);
    console.log(res.body);
    expect(
      res.body.message,
      `The response body should have a property called "message" with the correct value, but it doesn't, it should be "New password must be at least 8 characters" change the response body in the function that handles the PATCH /api/v1/profile/password route`,
      options
    ).toBe("New password must be at least 8 characters");
  });

  it("should not login with the old password", async () => {
    const res = await request(app).post("/api/v1/login").send({
      username: user.email,
      password: user.password,
    });
    expect(
      res.statusCode,
      `The status code should be 400, but it is "${res.statusCode}", change the status code in the function that handles the POST /api/v1/login route`,
      options
    ).toBe(400);
    expect(
      res.body.message,
      `The response body should have a property called "message" with the correct value, but it doesn't, it should be "Incorrect Username or Password" change the response body in the function that handles the POST /api/v1/login route`,
      options
    ).toBe("Incorrect Username or Password");
  });
});

afterAll(async () => {
  const collections = await mongoose.connection.db.collections();
  for (let collection of collections) {
    await collection.drop();
  }
  await disconnectDB();
});

async function connectDB() {
  return mongoose.connect(process.env.MONGODB_URI_TEST, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
  });
}

async function disconnectDB() {
  await mongoose.connection.close();
}
