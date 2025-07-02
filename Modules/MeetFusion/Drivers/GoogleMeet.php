<?php

namespace Modules\MeetFusion\Drivers;

use Modules\MeetFusion\Contracts\MeetFusionDriverInterface;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\ConferenceSolutionKey;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\EntryPoint;
use Google_Service_Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GoogleMeet implements MeetFusionDriverInterface
{
    protected $clientCredentials;

    public function setKeys($credentials) {
        $this->clientCredentials = $credentials;
    }

    public function createMeeting($eventData)
    {
        try {
            $client = new Client($this->clientCredentials);
            $meetingLink = null;
            if (!empty($eventData['calendar_id']) && !empty($eventData['event_id'])) {
                $client->setAccessToken($eventData['access_token']);
                if ($client->isAccessTokenExpired()) {
                    $refreshToken = $client->getRefreshToken();
                    if ($refreshToken) {
                        $client->fetchAccessTokenWithRefreshToken($refreshToken);
                        $client->getAccessToken();
                    }
                }
                $service = new Calendar($client);
                $event = $service->events->get($eventData['calendar_id'], $eventData['event_id']);
                if (!empty($eventData['meeting_link'])) {
                    $newDescription = __('meetfusion::meetfusion.join_meeting') . $eventData['meeting_link'] . '<br />'. $event->getDescription();
                    $event->setDescription($newDescription);
                    $event->setLocation($eventData['meeting_link']);
                    
                    $event->setGuestsCanInviteOthers(false);
                    $event->setGuestsCanModify(false);
                    $event->setGuestsCanSeeOtherGuests(false);
                    
                    $updatedEvent = $service->events->patch($eventData['calendar_id'], $eventData['event_id'], $event);
                    $meetingLink = $eventData['meeting_link'];
                } else {
                    $conferenceData = new ConferenceData();
                    $conferenceRequest = new CreateConferenceRequest();
                    $conferenceRequest->setRequestId('lr-' . uniqid());
                    $solutionKey = new ConferenceSolutionKey();
                    $solutionKey->setType('hangoutsMeet');
                    $conferenceRequest->setConferenceSolutionKey($solutionKey);
                    $conferenceData->setCreateRequest($conferenceRequest);
                    
                    $event->setConferenceData($conferenceData);
                    $event->setGuestsCanInviteOthers(false);
                    $event->setGuestsCanModify(false);
                    $event->setGuestsCanSeeOtherGuests(false);

                    $updatedEvent = $service->events->update($eventData['calendar_id'], $eventData['event_id'], $event, ['conferenceDataVersion' => 1]);
                    $meetingLink = $updatedEvent->getHangoutLink();
                }
                
                if (empty($meetingLink)) {
                    Log::error(__('meetfusion::meetfusion.failed_create_google_meet_link'));
                    throw new \Exception(__('meetfusion::meetfusion.failed_create_google_meet_link'));
                }
            }

            if (!empty($eventData['booking_calendar_id']) && !empty($eventData['booking_event_id']) && !empty($eventData['meeting_link'])) {
                $meetingLink = $eventData['meeting_link'];
                $client->setAccessToken($eventData['booking_access_token']);
                if ($client->isAccessTokenExpired()) {
                    $refreshToken = $client->getRefreshToken();
                    if ($refreshToken) {
                        $client->fetchAccessTokenWithRefreshToken($refreshToken);
                        $client->getAccessToken();
                    }
                }
                $service = new Calendar($client);
                $bookingEvent = $service->events->get($eventData['booking_calendar_id'], $eventData['booking_event_id']);
                $conferenceData = $bookingEvent->getConferenceData();

                if (empty($conferenceData)) {
                    $conferenceData = new ConferenceData();
                    $conferenceRequest = new CreateConferenceRequest();
                    $conferenceRequest->setRequestId('lr-' . uniqid());
                    $solutionKey = new ConferenceSolutionKey();
                    $solutionKey->setType('hangoutsMeet');
                    $conferenceRequest->setConferenceSolutionKey($solutionKey);
                    $conferenceData->setCreateRequest($conferenceRequest);
                }

                $entryPoint = new EntryPoint();
                $entryPoint->setEntryPointType('video'); 
                $entryPoint->setUri($meetingLink); 
                $conferenceData->setEntryPoints([$entryPoint]);
                $bookingEvent->setConferenceData($conferenceData);

                $bookingEvent->setGuestsCanInviteOthers(false);
                $bookingEvent->setGuestsCanModify(false);
                $bookingEvent->setGuestsCanSeeOtherGuests(false);

                $updatedEvent = $service->events->update($eventData['booking_calendar_id'], $eventData['booking_event_id'], $bookingEvent, ['conferenceDataVersion' => 1]);
            }

            return ['status' => Response::HTTP_OK, 'data' => ['link' => $meetingLink]];
        } catch (Google_Service_Exception $ex) {
            Log::error($ex);
            return ['status' => $ex->getCode(), 'message' => $ex->getMessage()];
        } catch (\Exception $ex) {
            Log::error($ex);
            return ['status' =>$ex->getCode(), 'message' => $ex->getMessage()];
        }
    }
}