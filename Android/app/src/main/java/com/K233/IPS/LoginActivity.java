package com.K233.IPS;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.text.TextUtils;
import android.text.method.HideReturnsTransformationMethod;
import android.text.method.PasswordTransformationMethod;
import android.util.Log;
import android.view.View;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.AuthFailureError;
import com.android.volley.DefaultRetryPolicy;
import com.android.volley.NetworkResponse;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.RetryPolicy;
import com.android.volley.ServerError;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.HttpHeaderParser;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.util.HashMap;
import java.util.Map;
import java.util.Set;

public class LoginActivity extends AppCompatActivity {

    private TextView registerBtn;
    private EditText email_ET, password_ET;
    private Button loginBtn;
    private CheckBox showHideBtn;
    ProgressBar progressBar;
    private String email, password;
//    UtilService utilService;
//    SharedPreferences sharedPreferenceClass;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getWindow().setSoftInputMode(WindowManager.LayoutParams.SOFT_INPUT_ADJUST_PAN);
        setContentView(R.layout.activity_login);

        showHideBtn = findViewById(R.id.showHideBtn);
        showHideBtn.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton compoundButton, boolean value) {
                if (value) {
                    // Show Password
                    password_ET.setTransformationMethod(HideReturnsTransformationMethod.getInstance());
                } else {
                    // Hide Password
                    password_ET.setTransformationMethod(PasswordTransformationMethod.getInstance());
                }
            }
        });

//        registerBtn = findViewById(R.id.signUpBtn);
//        registerBtn.setOnClickListener(new View.OnClickListener() {
//            @Override
//            public void onClick(View view) {
//                startActivity(new Intent(getApplicationContext(), RegisterActivity.class));
//                finish();
//            }
//        });

        loginBtn = findViewById(R.id.signInBtn);
        progressBar = findViewById(R.id.progress_bar);
        email_ET = findViewById(R.id.email_ET);
        password_ET = findViewById(R.id.password_ET);
//        utilService = new UtilService();
//        sharedPreferenceClass = new SharedPreferences();

        loginBtn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
//                utilService.hideKeyboard(view, LoginActivity.this);
                email = email_ET.getText().toString();
                password = password_ET.getText().toString();

                if (validate(view)) {
                    loginUser(view);
                }
            }
        });
    }

    private void loginUser(View view) {

        progressBar.setVisibility(View.VISIBLE);

        final HashMap<String, String> params = new HashMap<>();
        params.put("email", email);
        params.put("password", password);

        String dummySend= "https://webhook.site/964a28db-2407-4e0a-918e-dc18efe2120d";
//        String dummyReceive= "https://jsonplaceholder.typicode.com/todos/1";
//        String apiKey= "https://webhook.site/964a28db-2407-4e0a-918e-dc18efe2120d";

//        JsonObjectRequest jsonor = new JsonObjectRequest(Request.Method.GET, apiKey, new JSONObject(params), new Response.Listener<JSONObject>() {
//            @Override
//            public void onResponse(JSONObject response) {
//                Toast.makeText(LoginActivity.this, response.toString(), Toast.LENGTH_SHORT).show();
//            }
//        }, new Response.ErrorListener() {
//            @Override
//            public void onErrorResponse(VolleyError error) {
//                Toast.makeText(LoginActivity.this, "json failed", Toast.LENGTH_SHORT).show();
//            }
//        });
//
//        RequestQueue rq  = Volley.newRequestQueue(this);
//        rq.add(jsonor);

        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(Request.Method.POST, dummySend, new JSONObject(params), new Response.Listener<JSONObject>() {
            @Override
            public void onResponse(JSONObject response) {
//                try {
                    Log.e("2", "success");
//                    if (response.getBoolean("success")){
//                        Log.e("3", "milestone");
//                        String token = response.getString("token");
//                        SharedPreferences sharedPreferences = getSharedPreferences("MySharedPref", MODE_PRIVATE);
//                        SharedPreferences.Editor myEdit = sharedPreferences.edit();
//                        myEdit.putString("token", token);
//                        myEdit.apply();
//
//                        Toast.makeText(LoginActivity.this, token, Toast.LENGTH_SHORT).show();
//                        startActivity(new Intent(LoginActivity.this, ParkActivity.class));
//                        finish();
//                    }
//                    progressBar.setVisibility(View.GONE);
//                } catch (JSONException e) {
//                    Log.e("3err", "milestone");
//                    e.printStackTrace();
//                    progressBar.setVisibility(View.GONE);
//                }
            }
        }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {
                Log.e("2", "fail");
//                NetworkResponse response = error.networkResponse;
//                if (error instanceof ServerError && response != null){
//                    try {
//                        String res = new String(response.data, HttpHeaderParser.parseCharset(response.headers, "utf-8"));
//
//                        JSONObject obj = new JSONObject(res);
//                        Toast.makeText(LoginActivity.this, obj.getString("msg"), Toast.LENGTH_SHORT).show();
//
//                        progressBar.setVisibility(View.GONE);
//
//                    }catch (JSONException | UnsupportedEncodingException je){
//                        je.printStackTrace();
//                        progressBar.setVisibility(View.GONE);
//                    }
//                }

            }
        }) {
            @Override
            public Map<String, String> getHeaders() throws AuthFailureError {
                HashMap<String, String> headers = new HashMap<>();
                headers.put("Content-Type","application/json");

                return params;
            }
        };

//set retry policy
        Log.e("4", "milestone");
        int socketTime = 3000;
        RetryPolicy policy = new DefaultRetryPolicy(socketTime, DefaultRetryPolicy.DEFAULT_MAX_RETRIES,
                DefaultRetryPolicy.DEFAULT_BACKOFF_MULT);
        jsonObjectRequest.setRetryPolicy(policy);

//request add
        RequestQueue requestQueue = Volley.newRequestQueue(this);
        requestQueue.add(jsonObjectRequest);

    }

    public boolean validate(View view) {
        boolean isValid;

        if (!TextUtils.isEmpty(email)) {
            if (!TextUtils.isEmpty(password)) {
                isValid = true;
            } else {
//                utilService.showSnackbar(view, "Please enter password");
                Toast.makeText(this, "Please enter password", Toast.LENGTH_SHORT).show();
                isValid = false;
            }
        } else {
//            utilService.showSnackbar(view, "Please enter email");
            Toast.makeText(this, "Please enter email", Toast.LENGTH_SHORT).show();
            isValid = false;
        }
        return isValid;
    }

    @Override
    protected void onStart() {
        super.onStart();

        SharedPreferences snapshot_pref = getSharedPreferences("user_snapshot", MODE_PRIVATE);
        if (snapshot_pref.contains("token")){
            startActivity(new Intent(LoginActivity.this, ParkActivity.class));
            finish();
        }
    }
}