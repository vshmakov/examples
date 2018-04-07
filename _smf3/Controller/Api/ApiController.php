/**
*@Route("/profiles/choose", name="chooseProfile")
*@Method("POST")
*/
public function chooseProfileAction(Request $request) {
$profile=er('p')->getCurrentUserOrPublicProfileByIdOrNull($request->request->get('profileId')) ?? throwNotFoundExseption();
er('u')->getCurrentUserOrGuest()->setProfile($profile);
em()->flush();
return $this->json($profile->getId());
}

}