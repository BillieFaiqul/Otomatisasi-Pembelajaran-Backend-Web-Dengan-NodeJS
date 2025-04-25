const mongoose = require("mongoose");
const request = require("supertest");
const app = require("../../app");
const jwt = require("jsonwebtoken");

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

let token;
let returnedUser;

beforeAll(async () => {
  await connectDB().then(
    async () => {
      console.log("Database connected successfully");
    },
    (err) => {
      console.log("There is problem while connecting database " + err);
    }
  );
});

describe("Testing POST /api/v1/login", () => {
  // Testing the application login endpoint
  it("should register a new user", async () => {
    const res = await request(app).post("/api/v1/register").send(user);
    expect(
      res.statusCode,
      `The status code should be 201, but it is "${res.statusCode}", change the status code in the function that handles the POST /api/v1/register route`,
      options
    ).toBe(201);
  });

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
    expect(
      res.body,
      `The response body should have a property called "user", but it doesn't, change the response body in the function that handles the POST /api/v1/login route`,
      options
    ).toHaveProperty("user");
    returnedUser = res.body.user;
    expect(
      returnedUser,
      `The data returned in the response don't match the data that was sent in the request, change the response body in the function that handles the POST /api/v1/login route`,
      options
    ).toMatchObject({
      name: user.name,
      username: user.username,
      email: user.email,
    });
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    expect(
      decoded,
      `The token returned in the response body is not valid, change the response body in the function that handles the POST /api/v1/login route`,
      options
    ).toMatchObject({
      id: returnedUser.id,
      iat: expect.any(Number),
      exp: expect.any(Number),
    });
  });

  it("should login a user with email", async () => {
    const res = await request(app).post("/api/v1/login").send({
      username: user.email,
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
    expect(
      res.body,
      `The response body should have a property called "user", but it doesn't, change the response body in the function that handles the POST /api/v1/login route`,
      options
    ).toHaveProperty("user");
    returnedUser = res.body.user;
    expect(
      returnedUser,
      `The data returned in the response don't match the data that was sent in the request, change the response body in the function that handles the POST /api/v1/login route`,
      options
    ).toMatchObject({
      name: user.name,
      username: user.username,
      email: user.email,
    });
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    expect(
      decoded,
      `The token returned in the response body is not valid, change the response body in the function that handles the POST /api/v1/login route`,
      options
    ).toMatchObject({
      id: returnedUser.id,
      iat: expect.any(Number),
      exp: expect.any(Number),
    });
  });

  it("should not login a user with wrong username", async () => {
    const res = await request(app).post("/api/v1/login").send({
      username: "wrongusername",
      password: user.password,
    });
    expect(
      res.statusCode,
      `The status code should be "400", but it is "${res.statusCode}", change the status code in the function that handles the POST /api/v1/login route`,
      options
    ).toBe(400);
    expect(
      res.body,
      `The response body should have a property called "message", but it doesn't, change the response body in the function that handles the POST /api/v1/login route`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The message returned in the response "${res.body.message}" is not correct, it should be "Invalid Username or Password", change the response body in the function that handles the POST /api/v1/login route`,
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
