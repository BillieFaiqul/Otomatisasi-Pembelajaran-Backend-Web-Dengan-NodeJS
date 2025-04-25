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

describe("Testing GET /api/v1/profile", () => {
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

  it("should return a user with the given token", async () => {
    const res = await request(app)
      .get("/api/v1/profile")
      .set("Authorization", `Bearer ${token}`);

    expect(
      res.statusCode,
      `The status code should be 200, but it is "${res.statusCode}", change the status code in the function that handles the GET /api/v1/profile route`,
      options
    ).toBe(200);
    expect(
      res.body,
      `The response body should have a property called "user", but it doesn't, change the response body in the function that handles the GET /api/v1/profile route`,
      options
    ).toHaveProperty("user");
    returnedUser = res.body.user;

    expect(
      [returnedUser.name, returnedUser.username, returnedUser.email],
      `The returned user should have the same data as the user in the database, but it doesn't, change the response body in the function that handles the GET /api/v1/profile route`,
      options
    ).toMatchObject([user.name, user.username, user.email]);
    expect(
      returnedUser,
      `The returned user should not have a property called "password", but it does, change the response body in the function that handles the GET /api/v1/profile route`,
      options
    ).not.toHaveProperty("password");
  });

  it("should return a 401 status code if the token is invalid", async () => {
    const res = await request(app)
      .get("/api/v1/profile")
      .set("Authorization", `Bearer ${token}a`);
    expect(
      res.statusCode,
      `The status code should be 401 when the token is wrong, but it is "${res.statusCode}", change the status code in the function that handles the GET /api/v1/profile route`,
      options
    ).toBe(401);
    expect(
      res.body,
      `The response body should have a property called "message", but it doesn't, change the response body in the function that handles the GET /api/v1/profile route`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response body should have a property called "message" with the value "Unauthorized", but it doesn't, change the response body in the function that handles the GET /api/v1/profile route`,
      options
    ).toBe("Unauthorized");
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
