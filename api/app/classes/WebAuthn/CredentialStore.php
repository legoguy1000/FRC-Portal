<?php
namespace FrcPortal\WebAuthn;

use Illuminate\Database\Eloquent\Model as Eloquent;
use MadWizard\WebAuthn\Credential\CredentialId;
use MadWizard\WebAuthn\Credential\CredentialRegistration;
use MadWizard\WebAuthn\Credential\CredentialStoreInterface;
use MadWizard\WebAuthn\Credential\UserCredentialInterface;
use MadWizard\WebAuthn\Credential\UserCredential as WebAuthnUserCredential;
use MadWizard\WebAuthn\Credential\UserHandle;
use MadWizard\WebAuthn\Crypto\CoseKey;

class CredentialStore implements CredentialStoreInterface {

  public function findCredential(CredentialId $credentialId): ?UserCredentialInterface {
    $cred = \FrcPortal\UserCredential::where('credential_id', $credentialId->toString())->first();
    if(!is_null($cred)) {
      $credential = CredentialId::fromString($cred->credential_id); // CredentialId
      $public_key = CoseKey::fromString($cred->public_key); //CoseKeyInterface
      $user_handle = UserHandle::fromString($cred->user_handle); //UserHandle
      return new WebAuthnUserCredential($credential, $public_key, $user_handle);
    }
    return null;
      //return $_SESSION['credentials'][$credentialId->toString()]['credential'] ?? null;
  }

  public function registerCredential(CredentialRegistration $credential):void {
    //die(json_encode(new UserCredential($credential->getCredentialId(), $credential->getPublicKey(), $credential->getUserHandle())));
    $credentialId = $credential->getCredentialId()->toString(); // CredentialId
    $public_key = $credential->getPublicKey()->toString(); //CoseKeyInterface
    $user_handle = $credential->getUserHandle()->toString(); //UserHandle
    //var_dump($credential->getPublicKey());
    $cred = new \FrcPortal\UserCredential();
    $cred->credential_id = $credentialId;
    $cred->public_key = $public_key;
    $cred->user_handle = $user_handle;
    $cred->user_id = $credential->getUserHandle()->toBinary();
    $cred->save();
  }

  public function getSignatureCounter(CredentialId $credentialId): ?int {
      return $_SESSION['credentials'][$credentialId->toString()]['counter'] ?? null;
  }

  public function updateSignatureCounter(CredentialId $credentialId, int $counter): void {
      $_SESSION['credentials'][$credentialId->toString()]['counter'] = $counter;
  }

  public function getUserCredentialIds(UserHandle $userHandle) : array {
    $data = \FrcPortal\UserCredential::where('user_handle', $userHandle->toString())->get()->pluck('credential_id');
    return $data->map(function ($cred) {
        return CredentialId::fromString($cred);
    })->all();
  }
}
