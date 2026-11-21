<?php
function readUsers(): array { $f=__DIR__.'/../data/users.json'; return json_decode(file_get_contents($f),true)?:[]; }
function saveUsers(array $u): void { file_put_contents(__DIR__.'/../data/users.json', json_encode($u,JSON_PRETTY_PRINT)); }
function findUserByEmail(string $e): ?array { foreach(readUsers() as $u) if($u['email']===$e) return $u; return null; }
function findUserById(int $id): ?array { foreach(readUsers() as $u) if($u['id']===$id) return $u; return null; }
function addUser(string $name,string $email,string $password): array {
    $users=readUsers(); $id=count($users)?max(array_column($users,'id'))+1:1;
    $u=['id'=>$id,'name'=>$name,'email'=>$email,'password'=>$password,'role'=>'student'];
    $users[]=$u; saveUsers($users); return $u;
}
