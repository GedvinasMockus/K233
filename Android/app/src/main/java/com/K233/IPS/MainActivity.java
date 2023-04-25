package com.K233.IPS;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import org.json.JSONException;
import org.json.JSONArray;
import org.json.JSONObject;

import com.K233.IPS.Retrofit.INodeJs;
import com.K233.IPS.Retrofit.RetrofitClient;

import io.reactivex.android.schedulers.AndroidSchedulers;
import io.reactivex.disposables.CompositeDisposable;
import io.reactivex.functions.Consumer;
import io.reactivex.schedulers.Schedulers;
import retrofit2.Retrofit;


public class MainActivity extends AppCompatActivity {

    private Button btnLogin;
    private Button btnReg;
    private EditText edtEmail;
    private EditText edtPass;
    SharedPreferences savedData;
    INodeJs myAPI;
    CompositeDisposable compositeDisposable = new CompositeDisposable();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        Retrofit retrofit = RetrofitClient.getInstance();
        myAPI = retrofit.create(INodeJs.class);

        btnReg = findViewById(R.id.regBtn);
        btnLogin = findViewById(R.id.btnLogin);
        edtEmail = findViewById(R.id.edtEmail);
        edtPass = findViewById(R.id.edtPass);

        savedData = getSharedPreferences("UserData", Context.MODE_PRIVATE);

        String checkUUID = savedData.getString("UUID", "Klaida");
        int checkID = savedData.getInt("ID", -1);
        if (checkID != -1 && !checkUUID.equals("Klaida"))
        {
            Intent intent = new Intent(MainActivity.this, ParkActivity.class);
            startActivity(intent);
        }

        btnLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                loginUser(edtEmail.getText().toString(), edtPass.getText().toString());
            }
        });
        btnReg.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent viewIntent = new Intent("android.intent.action.VIEW", Uri.parse("http://78.62.39.220/Register"));
                startActivity(viewIntent);
            }
        });
    }

    private void loginUser(String email, String password) {
        SharedPreferences.Editor editor = savedData.edit();
        compositeDisposable.add(myAPI.loginUser(email, password)
                .subscribeOn(Schedulers.io())
                .observeOn(AndroidSchedulers.mainThread())
                .subscribe(new Consumer<String>() {
                    @Override
                    public void accept(String s) throws Exception {
                        int userID = -1;
                        String userUUID = "";
                        String userEmail = "";
                        Log.e("TAG", s);
                        if(s.contains("id")) {
                            try {
                                JSONArray jsonArr = new JSONArray(s);
                                JSONObject json = jsonArr.getJSONObject(0);
                                userID = json.getInt("id");
                                userUUID = json.getString("uuid");
                                userEmail = json.getString("email");
                            }
                            catch (JSONException e) {
                                Log.e("JSON klaida", "Nepavyko nuskaityti prisijungimo patvirtinimo");
                                e.printStackTrace();
                            }
                            editor.putString("UUID", userUUID);
                            editor.putInt("ID", userID);
                            editor.putString("email", userEmail);
                            editor.commit();
                            Intent intent = new Intent(MainActivity.this, ParkActivity.class);
                            startActivity(intent);
                        }else
                            Toast.makeText(MainActivity.this,""+s,Toast.LENGTH_SHORT).show();
                    }
                }));
    }
}