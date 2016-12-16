<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use Auth;
use DB;

class MailController extends Controller
{
public function __construct()
   {
       $this->middleware('jwt.auth');
   }    

    public function compose(Request $request)
    {
        $attachment = $request->file('attachment');
        $attachment->move('uploads',$attachment->getClientOriginalName());
        $id = DB::table('mails')->insert([ 'subject' => $request->subject,'body'=>$request->body,'attachment'=>$attachment->getClientOriginalName()]);
        DB::table('inbox')->insert(['from'=> Auth::User()->email,'to' => $request->to,'mail_id'=>$id,'is_read'=>false]);
        return response()->json(['success'=>true,'msg'=>'Mail Sent']);
    }

    public function forward(Request $request)
    {
        DB::table('inbox')->insert(['from'=> Auth::User()->email,'to' => $request->to,'mail_id'=>$request->id,'mail_type'=>2,'is_read'=>false]);
        return response()->json(['success'=>true,'msg'=>'Mail Sent']);
    } 

    public function reply(Request $request)
    {
        DB::table('inbox')->insert(['from'=> Auth::User()->email,'to' => $request->to,'mail_id'=>$request->id,'parent_id'=>$request->parent_id,'mail_type'=>1,'is_read'=>false]);
        return response()->json(['success'=>true,'msg'=>'Mail Sent']);
    }     

    public function makeRead($id)
    {
        DB::table('users')->where('id', $id)->update(['is_read' => true]);
        return response()->json(['success'=>true,'msg'=>'Success']);
    }

    public function mailCounts(){
        $inbox = DB::table('inbox')->where('to', Auth::User()->email)->count();
        $sent = DB::table('inbox')->where('from', Auth::User()->email)->count();
        $draft = DB::table('draft')->where('from', Auth::User()->email)->count();
        $trash = DB::table('trash')->where('from', Auth::User()->email)->orWhere('to', Auth::User()->email)->count();
        return response()->json(['inbox'=>$inbox,'sent'=>$sent,'draft'=>$draft,'trash'=>$trash]);
    }

    public function inbox($id='')
    {
        if($id !=''){
            $inbox = DB::table('inbox')
                    ->where('inbox.id',$id)
                    ->join('users as fuser', 'fuser.email', '=', 'inbox.to')
                    ->join('users as tuser', 'tuser.email', '=', 'inbox.from')
                    ->join('mails', 'mails.id', '=', 'inbox.mail_id')   
                    ->select('fuser.name as fromName','tuser.name as toName', 'mails.subject', 'mails.body','mails.attachment', 'inbox.*')->first();
        }
        else{
            $inbox = DB::table('inbox')
                    ->where('inbox.to', Auth::User()->email)
                    ->join('users as fuser', 'fuser.email', '=', 'inbox.to')
                    ->join('users as tuser', 'tuser.email', '=', 'inbox.from')
                    ->join('mails', 'mails.id', '=', 'inbox.mail_id')   
                    ->select('fuser.name as fromName','tuser.name as toName', 'mails.subject', 'mails.body','mails.attachment', 'inbox.*')->get();
        }
        return response()->json($inbox);
    }

    public function sent($id='')
    {
        if($id !=''){
            $sent = DB::table('inbox')
                    ->where('inbox.id',$id)
                    ->join('users as fuser', 'fuser.email', '=', 'inbox.to')
                    ->join('users as tuser', 'tuser.email', '=', 'inbox.from')
                    ->join('mails', 'mails.id', '=', 'inbox.mail_id')
                    ->select('fuser.name as fromName','tuser.name as toName', 'mails.subject', 'mails.body','mails.attachment', 'inbox.*')->first();
        }
        else{
            $sent = DB::table('inbox')
                    ->where('inbox.from', Auth::User()->email)
                    ->join('users as fuser', 'fuser.email', '=', 'inbox.to')
                    ->join('users as tuser', 'tuser.email', '=', 'inbox.from')
                    ->join('mails', 'mails.id', '=', 'inbox.mail_id')   
                    ->select('fuser.name as fromName','tuser.name as toName', 'mails.subject', 'mails.body','mails.attachment', 'inbox.*')->get();
        }
        return response()->json($sent);
    }

    public function draft($id='')
    {
        if($id !=''){
        $draft = DB::table('draft')
                    ->where('draft.id',$id)
                    ->join('users as fuser', 'fuser.email', '=', 'draft.to')
                    ->join('users as tuser', 'tuser.email', '=', 'draft.from')
                    ->join('mails', 'mails.id', '=', 'draft.mail_id')
                    ->select('fuser.name as fromName','tuser.name as toName', 'mails.subject', 'mails.body','mails.attachment', 'draft.*')->first();
        }
        else{
        $draft = DB::table('draft')
                    ->where('draft.from', Auth::User()->email)
                    ->join('users as fuser', 'fuser.email', '=', 'draft.to')
                    ->join('users as tuser', 'tuser.email', '=', 'draft.from')
                    ->join('mails', 'mails.id', '=', 'draft.mail_id')
                    ->select('fuser.name as fromName','tuser.name as toName', 'mails.subject', 'mails.body','mails.attachment', 'draft.*')->get();
        }
        return response()->json($draft);
    }

    public function trash($id='')
    {
        if($id !=''){
          $trash = DB::table('trash')
                    ->where('trash.id',$id)
                    ->join('users as fuser', 'fuser.email', '=', 'trash.to')
                    ->join('users as tuser', 'tuser.email', '=', 'trash.from')
                    ->join('mails', 'mails.id', '=', 'trash.mail_id')
                    ->select('fuser.name as fromName','tuser.name as toName', 'mails.subject', 'mails.body','mails.attachment', 'trash.*')->first();
        }
        else{
            $trash = DB::table('trash')
                    ->where('trash.from', Auth::User()->email)
                    ->join('users as fuser', 'fuser.email', '=', 'trash.to')
                    ->join('users as tuser', 'tuser.email', '=', 'trash.from')
                    ->join('mails', 'mails.id', '=', 'trash.mail_id')
                    ->select('fuser.name as fromName','tuser.name as toName', 'mails.subject', 'mails.body','mails.attachment', 'trash.*')->get();
        }
        return response()->json($trash);
    }           
}
