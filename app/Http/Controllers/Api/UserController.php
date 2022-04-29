<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = strtolower($request->email);
            $user->role = 'C';
            $user->password = Hash::make($request->password);
            $user->created_by = 1;
            $user->updated_by = 1;
            $user->created_at = new DateTime();

            $user->save();

            $result = array(
                'error' => 0,
                'message' => 'User register with success.',
            );

            return response()->json($result);
        } catch (\Exception  $exception) {
            return response()->json(["error" => 1, "message" => $exception->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        if (!is_numeric($request->id)) {
            $result = array(
                'error' => 1,
                'msg' => 'Dados Inválidos.',
            );

            return response()->json($result);
        }


        $messages = [
            'name.required' => "O campo nome  é obrigatório.",
            'name.min' => "O campo nome precisa ter no mínimo 2 caracteres.",
            'name.max' => "O campo nome precisa ter no máximo 100 caracteres.",
            'last_name.required' => "O campo sobrenome é obrigatório.",
            'last_name.min' => "O campo nome precisa ter no mínimo 2 caracteres.",
            'last_name.max' => "O campo sobrenome precisa ter no máximo 100 caracteres.",
            'email.required' => "O campo e-mail é obrigatório.",
            'email.unique' => "O e-mail informado, já esta cadastrado.",
            'email.email' => "O e-mail informado não é um e-mail válido.",
            'email.max' => "O campo sobrenome precisa ter no máximo 255 caracteres.",
            'login.required' => "O campo login é obrigatorio.",
            'login.unique' => "O login informado, já esta cadastrado.",
            'login.min' => "O campo login precisa ter no mínimo 5 caracteres.",
            'login.max' => "O campo login precisa ter no máximo 15 caracteres.",
            'status.required' => "O campo status é obrigatorio.",
            'status.min' => "O campo status precisa ter no mínimo 1 caracter.",
            'status.max' => "O campo status precisa ter no máximo 1 caracter.",
            'status.alpha' => "O campo status precisa ser ATIVO (A), INATIVO (I) ou BLOQUEADO (B).",
            'password.required' => 'O campo senha é obrigatório',
            'password.min' => 'O campo senha precisa ter no mínimo 6 caracteres',
        ];


        $user = User::find($request->id);
        if ($user == null) {
            $result = array(
                'error' => 1,
                'message' => 'Dados de acesso inválido.',
            );

            return response()->json($result, 400);
        }

        $user->rules['email'] = $user->rules['email'] . ',email,' . $request->id.',id,deleted_at,NULL';
        $user->rules['login'] = $user->rules['login'] . ',login,' . $request->id.',id,deleted_at,NULL';


        $validator = \Validator::make($request->all(), $user->rules, $messages);

        if ($validator->fails()) {
            $result = array(
                'error' => 1,
                'message' => trim($validator->errors()->all()[0]),
            );
            return response()->json($result);
        }


        try {
            $auth = JWTAuth::toUser($request->bearerToken());

            $user = User::find($request->id);
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->status = $request->status;
            $user->email = strtolower($request->email);
            $user->login = $request->login;

            if (isset($request->password)) {
                $user->password = Hash::make($request->password);
            }

            $user->updated_by = $auth->id;
            $user->created_at = new DateTime();
            $result = $user->update();


            $result = array(
                'error' => 0,
                'message' => 'Usuário alterado com sucesso.',
            );

            return response()->json($result);
        } catch (\Exception  $exception) {
            return response()->json(["error" => 1, "message" => $exception->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = User::find($request->id);
        if ($user == null) {
            $result = array(
                'error' => 1,
                'message' => 'Dados de acesso inválido.',
            );

            return response()->json($result, 400);
        }

        $user = User::find($request->id);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if (!is_numeric($request->id)) {
            $result = array(
                'error' => 1,
                'msg' => 'Dados Inválidos.',
            );

            return response()->json($result);
        }


        try {
            $auth = JWTAuth::toUser($request->bearerToken());

            $user = User::find($request->id);

            if ($user == null) {
                $result = array(
                'error' => 1,
                'message' => 'Dados de acesso inválido.',
            );

                return response()->json($result, 400);
            }

            $token = auth()->tokenById($request->id);
            //dd($token);
            JWTAuth::invalidate($token);

            $user->deleted_by = $auth->id;
            $user->deleted_at = new DateTime();
            $result = $user->update();

            $result = array(
                'error' => 0,
                'message' => 'Usuário excluído com sucesso.',
            );

            // $token = auth()->tokenById($request->id);


            return response()->json($result);
        } catch (\Exception  $exception) {
            return response()->json(["error" => 1, "message" => $exception->getMessage()]);
        }
    }

    public function rolesSync(Request $request, $id)
    {
        try {
            $rolesRequest = $request->roles;


            foreach ($rolesRequest as $value) {
                $roles[] = Role::find($value);
            }

            $user = User::find($id);
            if (!empty($roles)) {
                $user->syncRoles($roles);
            } else {
                $user->syncRoles(null);
            }

            $result = array(
                'error' => 0,
                'msg' => 'Perfis atribuidos ao usuário com sucesso.',
            );

            return response()->json($result);
        } catch (\Exception  $exception) {
            return response()->json(["error" => 1, "message" => $exception->getMessage()]);
        }
    }

    public function roles($id)
    {
        $data = User::find($id);
        $dataRoles = Role::where('deleted_at', null)->orderBy('name')
            ->get();

        $checked = 0;
        $rolesCan = array();
        foreach ($dataRoles as $key => $role) {
            if ($data->hasRole($role->name)) {
                $role->can = true;
                $checked++;
                $rolesCan[] = $role->id;
            } else {
                $role->can = false;
            }
        }

        if (count($dataRoles) == $checked) {
            $checkedAll = true;
        } else {
            $checkedAll = false;
        }

        return response()->json(["data" => $dataRoles, "can" => $rolesCan, "checkedAll" => $checkedAll]);
    }

    public function profile(Request $request)
    {
        $user = User::find($request->id);
        return response()->json(array_shift($user));
    }

    public function getPermissions(Request $request)
    {
        $user = User::find($request->id);

        $permissions = $user->getPermissionsViaRoles();

        $allPermissions = array();
        foreach ($permissions as $permission) {
            if (!in_array($permission->name, $allPermissions)) {
                $allPermissions[] = $permission->name;
            }
        }

        $user->permissions = $allPermissions;
        return response()->json([
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        if (isset($request->password)) {
            if (!$this->validatePassword($request->id, $request->password)) {
                $result = array(
                    'error' => 1,
                    'message' => 'Senha atual incorreta',
                    'field' => 'passwordRef'
                );
                return response()->json($result);
            }
        }

        $messages = [
            'name.required' => "O campo nome  é obrigatório. field nameRef",
            'name.min' => "O campo nome precisa ter no minimo 2 letras. field nameRef",
            'last_name.required' => "O campo sobrenome é obrigatório. field lastNameRef",
            'last_name.min' => "O campo sobrenome precisa ter no minimo 2 letras. field lastNameRef",
            'phone.required' => "O campo telefone  é obrigatório. field phoneRef",
            'phone.min' => "O campo telefone precisa ter 13 digitos. field phoneRef",
            'passwordNew.min' => "O campo senha dever ter no minimo 4 caracteres. field passwordNewRef",
            'passwordNew.confirmed' => "O campo nova senha não confere com o campo Confirme a nova senha.field passwordNewRef",
            'passwordNew.required' => "O campo nova senha é obrigatório.field passwordNewRef"
        ];

        $user = new User();

        $validator = \Validator::make($request->all(), $user->rules, $messages);


        if ($validator->fails()) {
            $arrMessage = explode('field', $validator->errors()->all()[0]);

            $result = array(
                'error' => 1,
                'message' => trim($arrMessage[0]),
                'field' => trim($arrMessage[1])
            );
            return response()->json($result);
        }


        try {
            $user = User::find($request->id);
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->phone = $request->phone;
            $user->updated_at = $auth->id;

            if (isset($request->password)) {
                $hashed_password = Hash::make($request->passwordNew);
                $user->password = $hashed_password;
            }



            $result = $user->update();

            $permissions = $user->getPermissionsViaRoles();

            $allPermissions = array();
            foreach ($permissions as $permission) {
                $allPermissions[] = $permission->name;
            }

            $userUpdate = new stdClass();
            $userUpdate->name = $user->name;
            $userUpdate->last_name = $user->last_name;
            $userUpdate->phone = $user->phone;
            $userUpdate->permissions = $allPermissions;
            $userUpdate->id = $user->id;
            $userUpdate->role = 'AllIn App';
            $userUpdate->branch_id = $user->branch_id;
            $userUpdate->updated_at = $user->updated_at;




            $result = array(
                'error' => 0,
                'message' => 'Perfil alterado com sucesso.',
                'user' => $userUpdate
            );

            return response()->json($result);
        } catch (\Exception  $exception) {
            return response()->json(["error" => 1, "message" => $exception->getMessage()]);
        }
    }

    public function validatePassword($id, $password)
    {
        $user = User::where('id', $id)->first();

        if ($user && Hash::check($password, $user->password)) {
            return true;
        } else {
            return false;
        }
    }


    public function sendEmailPassword($user, $password)
    {
        $dataMail = [
            'subject'   => 'Cadastro App AllIn',
            'view'      => 'email.Welcome.index',
            'name'      => $user->name,
            'email'      => $user->email,
            'code'      => $user->code,
            'email_from'      => 'contato@allinapp.com.br',
            'password'      => $password
        ];

        $dataMail['anexos'][] = null;

        $created = User::find($user->created_by);

        \Mail::to($user->email)
            ->bcc($created->email)
            ->send(new SendEmail($dataMail));

        if (\Mail::failures()) {
            return (\Mail::failures());
        // - aviso de email ao gestor do sistema
        } else {
            return response()->json(['message' => 'Senha enviada com sucesso.']);
        }
    }
}
