<?php


//Your current character name.
$PLAYER_NAME="Prisoner";

$DBDRIVER="postgresql";

//NPC Name. MUST MATCH their Skyrim in-game NPC name!
//If you are in the default profile YOU MUST leave it as "The Narrator"!
//You can change profiles by clicking the blue button in the top left.
$HERIKA_NAME="The Narrator";

//System Prompt. Defines the rules of the roleplay.
$PROMPT_HEAD="Let\'s roleplay in the Universe of Skyrim.";

//Player character description. Any info here will be known by all AI NPC's.
$PLAYER_BIOS="I\'m #PLAYER_NAME# of High Rock. I stand taller than most at six feet two, with shoulder-length dark brown hair and brown eyes that have seen their share of Skyrim\'s hardships and wonders.";

//1st half of NPC Bio. NPC Static Personality.
//Should be core traits and facts about a person that does not change.
$HERIKA_PERS="You are The Narrator in a Skyrim adventure. You will only talk to #PLAYER_NAME#. You refer to yourself as \'The Narrator\'. Only #PLAYER_NAME# can hear you. Your goal is to comment on #PLAYER_NAME#\'s playthrough, and occasionally give hints. NO SPOILERS. Talk about quests and last events.";

//2nd half of NPC Bio. NPC Dynamic Personality.
//Should be feelings and traits about a person that can change based on ingame events.
$HERIKA_DYNAMIC="";

//Will automatically update HERIKA_DYNAMIC during certain ingame events (such as sleeping).
$DYNAMIC_PROFILE=false;

//Enable Minime-T5 LLM. Helps dumber LLM's be more accurate with action and memory functions.
$MINIME_T5=false;

//Needs Minime-T5 enabled and running. Tamriel lore information will be added to the prompt.
$OGHMA_INFINIUM=false;

//Knowledge Classes assigned to the NPC.
$OGHMA_KNOWLEDGE="knowall";

//Number of Oghma keywords to extract from each response.
$OGHMA_AMOUNT="1";

//Rechat Rounds. Higher values will increase back-and-forth during conversations.
$RECHAT_H="2";

//Rechat Probability. Chance that an AI NPC will continue an ongoing conversation.
$RECHAT_P="50";

//Bored Event Probability. Chance of an AI NPC starting a random conversation.
$BORED_EVENT="30";

//Amount of context history (dialogue and events) that will be sent to LLM.
$CONTEXT_HISTORY="50";

//Whether the "I am alive.." response will trigger whenever you activate an AI NPC.
$ALIVE_MESSAGE=true;

//Whether the NPC will be aware of how long it has been since you last talked to them.
$TIME_AWARENESS=false;

//Send full contents of book instead of only the book title to the AI.
$BOOK_EVENT_FULL=true;

//The Narrator will be the only one to summarize books.
$BOOK_EVENT_ALWAYS_NARRATOR=false;

//Enable the Narrator.
$NARRATOR_TALKS=true;

//The Narrator will give you a quick recap after loading a save game.
$NARRATOR_WELCOME=true;

//Will trigger AI to talk about new objectives in your current active quest.
$QUEST_COMMENT=false;

//Chance that an AI Quest Comment will happen every time a quest updates.
$QUEST_COMMENT_CHANCE="10%";

//Include the current Dynamic AI Objective to the AI NPC's prompt.
$CURRENT_TASK=true;

//Pick which comments you want when ingame events happen.
$RPG_COMMENTS=["levelup","learn_shout","learn_word","absorb_soul","bleedout","combat_end","lockpick","sleep","keepmechecked"];

//Will issue animations for the NPC to play
$HERIKA_ANIMATIONS=true;

//Custom Language. The lang folder is in the CHIM Server. Leave it blank for English.
$CORE_LANG="";

//XTTS Only! Will offer a language field to LLM, and will try match to XTTSv2 language.
$LANG_LLM_XTTS=false;

//Timeout for AI requests.
$HTTP_TIMEOUT="15";

//Enforce a word limit for AI's responses. Leave as 0 to have no limit.
$MAX_WORDS_LIMIT="0";

//Reorders properties in the output JSON schema.
$JSON_DIALOGUE_FORMAT_REORDER=false;

//List of moods passed to LLM (comma separated).
$EMOTEMOODS="sassy,assertive,sexy,smug,kindly,lovely,seductive,sarcastic,sardonic,smirking,amused,default,assisting,irritated,playful,neutral,teasing,mocking";

//Default profile only! Custom instructions added when generating summaries for memories.
$SUMMARY_PROMPT='Focus on key events, tagging characters, locations, and factions accurately. Ensure memories align and maintain chronological order while foreshadowing future arcs. Prioritize player agency, and use environmental cues to enhance storytelling and continuity.';

//Default profile only! Instructions for updating HERIKA_DYNAMIC.
$DYNAMIC_PROMPT='(MANDATORY FORMAT – DO NOT ADD INTRO/OUTRO, HEADER OR EXTRA COMMENTARY I.E. NPC NAME. NO META-DATA, or DISCLAIMERS OF TASK! I.E. "UPDATING NPC PROFILE". Use concise, fragmented prose for the following output.) Last in-game date/time found: [date or "No date"] 1. RECENT HIGHLIGHTS (3–5 bullet points)    - Write one sentence per bullet with objective facts (locations, quest progress, important decisions). Re-list older relevant events DO NOT REMOVE ENTRIES that are still important. 2. EMOTIONAL/RELATIONAL UPDATES (1–2 lines per key person/faction)    - Describe the NPC\'s evolving feelings or stance toward the dragonborn, key individuals or groups. Always re-list unchanged but relevant relationships. 3. CONTINUING GOALS, CONFLICTS OR FEELINGS (2–3 bullet points)    - List ongoing arcs, dilemmas, objectives and goals with clear facts. Remove items only if resolved.';

//AI Service(s) to be used for most AI features.
//Select the service(s) you have configured for AI/LLM Connectors below.
$CONNECTORS=["openaijson","koboldcppjson","openrouterjson"];

//Is one of the SUMMARY connectors. Used for creating diary entries, dynamic profiles and summarized memories!
$CONNECTORS_DIARY="openrouter";

$CONNECTOR["openrouterjson"]["url"]='https://openrouter.ai/api/v1/chat/completions';	//OpenRouter API endpoint
$CONNECTOR["openrouterjson"]["model"]='meta-llama/llama-3.3-70b-instruct';	//<strong>Must be JSON/Instruct type of Model!</strong><br>FREE MODELS ARE NOT RECOMMENDED!<br>If you change model use buttons below to set new parameters!
$CONNECTOR["openrouterjson"]["PROVIDER"]='';	//Leave blank unless you want to manually select a provider from OpenRouter. It is case sensitive!
$CONNECTOR["openrouterjson"]["max_tokens"]='512';	//Maximum tokens to generate.
$CONNECTOR["openrouterjson"]["temperature"]=0.6;	//Temperature [0-2]
$CONNECTOR["openrouterjson"]["presence_penalty"]=0;	//Presence Penalty [(-2)-2]
$CONNECTOR["openrouterjson"]["frequency_penalty"]=0;	//Frequency Penalty [(-2)-2]
$CONNECTOR["openrouterjson"]["repetition_penalty"]=1.1;	//Repetition Penalty [0-2]
$CONNECTOR["openrouterjson"]["top_p"]=1;	//Top_P [0-1]
$CONNECTOR["openrouterjson"]["top_k"]=40;	//Top_K [0-100]
$CONNECTOR["openrouterjson"]["min_p"]=0;	//Min_P [0-1]
$CONNECTOR["openrouterjson"]["top_a"]=0;	//Top_A [0-1]
$CONNECTOR["openrouterjson"]["ENFORCE_JSON"]=true;	//Will attempt to enforce dumb LLM's to stay in JSON format. Leave as default (TRUE), only works with specific models.
$CONNECTOR["openrouterjson"]["PREFILL_JSON"]=false;	//Will attempt to prefill the JSON AI response for some dumber LLM's. Leave as default (FALSE), only works with specific models.
$CONNECTOR["openrouterjson"]["API_KEY"]='';	//OpenRouter key
$CONNECTOR["openrouterjson"]["xreferer"]='https://www.nexusmods.com/skyrimspecialedition/mods/89931';	//Stub needed header. Keep default.
$CONNECTOR["openrouterjson"]["xtitle"]='CHIM';	//Stub needed header. Keep default.
$CONNECTOR["openrouterjson"]["json_schema"]=false;	//Enable OpenRouter Json schema. Does not work with all models. You must set a provider that supports structured outputs. Requires ENFORCE_JSON to be true.

$CONNECTOR["openrouter"]["url"]='https://openrouter.ai/api/v1/chat/completions';	//OpenRouter API endpoint
$CONNECTOR["openrouter"]["model"]='meta-llama/llama-3.1-8b-instruct';	//Model to use.<br>FREE MODELS ARE NOT RECOMMENDED!<br>If you change model use buttons below to set new parameters!
$CONNECTOR["openrouter"]["PROVIDER"]='';	//Leave blank unless you want to manually select a provider from OpenRouter. It is case sensitive!
$CONNECTOR["openrouter"]["max_tokens"]='1024';	//Maximum tokens to generate for regular responses, NOT SUMMARIES.
$CONNECTOR["openrouter"]["temperature"]=0.9;	//Temperature [0-2]
$CONNECTOR["openrouter"]["presence_penalty"]=0;	//Presence Penalty [(-2)-2]
$CONNECTOR["openrouter"]["frequency_penalty"]=0;	//Frequency Penalty [(-2)-2]
$CONNECTOR["openrouter"]["repetition_penalty"]=0.9;	//Repetition Penalty [0-2]
$CONNECTOR["openrouter"]["top_p"]=1;	//Top_P [0-1]
$CONNECTOR["openrouter"]["top_k"]=0;	//Top_K [0-100]
$CONNECTOR["openrouter"]["min_p"]=0.1;	//Min_P [0-1]
$CONNECTOR["openrouter"]["top_a"]=0;	//Top_A [0-1]
$CONNECTOR["openrouter"]["API_KEY"]='';	//OpenRouter key
$CONNECTOR["openrouter"]["MAX_TOKENS_MEMORY"]='1024';	//Maximum tokens to generate when summarizing, diary entries, and dynamic profile updates.
$CONNECTOR["openrouter"]["xreferer"]='https://www.nexusmods.com/skyrimspecialedition/mods/89931';	//Stub needed header. Keep default.
$CONNECTOR["openrouter"]["xtitle"]='CHIM';	//Stub needed header. Keep default.

$CONNECTOR["openaijson"]["url"]='https://api.openai.com/v1/chat/completions';	//OpenAI API endpoint
$CONNECTOR["openaijson"]["model"]='gpt-4.1-mini';	//Model to use
$CONNECTOR["openaijson"]["max_tokens"]='512';	//Maximum tokens to generate
$CONNECTOR["openaijson"]["temperature"]=1;	//Temperature [0-2]
$CONNECTOR["openaijson"]["presence_penalty"]=1;	//Presence Penalty [(-2)-2]
$CONNECTOR["openaijson"]["frequency_penalty"]=0;	//Frequency Penalty [(-2)-2]
$CONNECTOR["openaijson"]["top_p"]=1;	//Top_P [0-1]
$CONNECTOR["openaijson"]["API_KEY"]='';	//OpenAI API key
$CONNECTOR["openaijson"]["MAX_TOKENS_MEMORY"]='1024';	//No longer used. Use SUMMARY connector for memory tokens instead.
$CONNECTOR["openaijson"]["json_schema"]=false;	//Enable OpenAI Json schema. Does not work with all OpenAI's models

$CONNECTOR["openai"]["url"]='https://api.openai.com/v1/chat/completions';	//OpenAI API endpoint
$CONNECTOR["openai"]["model"]='gpt-4o-mini';	//Model to use
$CONNECTOR["openai"]["max_tokens"]='1024';	//Maximum tokens to generate for regular responses, NOT SUMMARIES.
$CONNECTOR["openai"]["temperature"]=1;	//Temperature [0-2]
$CONNECTOR["openai"]["presence_penalty"]=1;	//Presence Penalty [(-2)-2]
$CONNECTOR["openai"]["frequency_penalty"]=0;	//Frequency Penalty [(-2)-2]
$CONNECTOR["openai"]["top_p"]=1;	//Top_P [0-1]
$CONNECTOR["openai"]["API_KEY"]='';	//OpenAI API key
$CONNECTOR["openai"]["MAX_TOKENS_MEMORY"]='1024';	//Maximum tokens to generate when summarizing, diary entries, and dynamic profile updates.

$CONNECTOR["google_openaijson"]["url"]='https://generativelanguage.googleapis.com/v1beta/openai/chat/completions';	//Google OpenAI API endpoint
$CONNECTOR["google_openaijson"]["model"]='gemini-1.5-flash';	//Model to use
$CONNECTOR["google_openaijson"]["max_tokens"]='512';	//Maximum tokens to generate
$CONNECTOR["google_openaijson"]["temperature"]=1;	//Temperature [0-2]
$CONNECTOR["google_openaijson"]["top_p"]=0.95;	//Top_P [0-1]
$CONNECTOR["google_openaijson"]["API_KEY"]='';	//Google API key
$CONNECTOR["google_openaijson"]["MAX_TOKENS_MEMORY"]='800';	//Maximum tokens to generate when summarizing, diary entries, and dynamic profile updates.
$CONNECTOR["google_openaijson"]["json_schema"]=false;	//Enable OpenAI Json schema.

$CONNECTOR["koboldcppjson"]["url"]='http://127.0.0.1:5001';	//Kobold should be running on the same machine as DwemerDistro!<br>Must use your computers, not the DwemerDistro, IP Address.<br>Can be found by running the command 'ipconfig' in your CMD prompt.<br>Example: http://your-local-ip:8008
$CONNECTOR["koboldcppjson"]["max_tokens"]='512';	//Maximum tokens to generate
$CONNECTOR["koboldcppjson"]["temperature"]=0.9;	//Temperature [0-2]
$CONNECTOR["koboldcppjson"]["rep_pen"]=1.12;	//Repetition Penalty [0-2]
$CONNECTOR["koboldcppjson"]["top_p"]=0.9;	//Top_P [0-1]
$CONNECTOR["koboldcppjson"]["min_p"]=0;	//Min_P [0-1]
$CONNECTOR["koboldcppjson"]["top_k"]=0;	//Top_K [0-100]
$CONNECTOR["koboldcppjson"]["PREFILL_JSON"]=false;	//Will prefill JSON, which is useful for some AI models, and destroy others.
$CONNECTOR["koboldcppjson"]["MAX_TOKENS_MEMORY"]='256';	//No longer used. Use SUMMARY connector for memory tokens instead.
$CONNECTOR["koboldcppjson"]["newline_as_stopseq"]=false;	//A new line in the output that will be considered a stop sequence. Recommended to leave as default.
$CONNECTOR["koboldcppjson"]["use_default_badwordsids"]=true;	//Unban End of Sentence (EOS) tokens. If set to false the LLM will stop generating when it detects an EOS token.
$CONNECTOR["koboldcppjson"]["eos_token"]='<|eot_id|>';	//EOS token LLM uses. Only works if use_default_badwordsids is enabled.
$CONNECTOR["koboldcppjson"]["template"]='chatml';	//Prompt Format. Specified in the HuggingFace model card
$CONNECTOR["koboldcppjson"]["grammar"]=false;	//Enforces use of JSON grammar. True to enforce (generation speed loss, but json format guaranteed). if false, the generation speed will be better but will depend on the model to produce valid JSON output.

$CONNECTOR["koboldcpp"]["url"]='http://127.0.0.1:5001';	//Kobold should be running on the same machine as DwemerDistro!<br>Must use your computers, not the DwemerDistro, IP Address.<br>Can be found by running the command 'ipconfig' in your CMD prompt.<br>Example: http://your-local-ip:8008
$CONNECTOR["koboldcpp"]["max_tokens"]='512';	//Maximum tokens to generate for regular responses, NOT SUMMARIES.
$CONNECTOR["koboldcpp"]["temperature"]=1;	//Temperature [0-2]
$CONNECTOR["koboldcpp"]["rep_pen"]=1;	//Repetition Penalty [0-2]
$CONNECTOR["koboldcpp"]["top_p"]=1;	//Top_P [0-1]
$CONNECTOR["koboldcpp"]["min_p"]=0.01;	//Min_P [0-1]
$CONNECTOR["koboldcpp"]["top_k"]=0;	//Top_K [0-100]
$CONNECTOR["koboldcpp"]["MAX_TOKENS_MEMORY"]='512';	//Maximum tokens to generate when summarizing, diary entries, and dynamic profile updates.
$CONNECTOR["koboldcpp"]["newline_as_stopseq"]=false;	//A new line in the output that will be considered a stop sequence. Recommended to leave as default.
$CONNECTOR["koboldcpp"]["use_default_badwordsids"]=false;	//Unban End of Sentence (EOS) tokens. If set to false the LLM will stop generating when it detects an EOS token.
$CONNECTOR["koboldcpp"]["eos_token"]='<|im_end|>';	//EOS token LLM uses. Only works if use_default_badwordsids is enabled.
$CONNECTOR["koboldcpp"]["template"]='chatml';	//Prompt Format. Specified in the HuggingFace model card

// Test connector for Anthropic Claude 3
$CONNECTOR["anthropic"]["url"]="https://api.anthropic.com/v1/messages";
$CONNECTOR["anthropic"]["model"]="claude-3-haiku-20240307";
$CONNECTOR["anthropic"]["max_tokens"]="100";
$CONNECTOR["anthropic"]["temperature"]=1;
$CONNECTOR["anthropic"]["API_KEY"]="";
$CONNECTOR["anthropic"]["MAX_TOKENS_MEMORY"]="512";
$CONNECTOR["anthropic"]["top_p"]=1;


//Text-to-Speech service options. Used to generate AI NPC voice.
$TTSFUNCTION="zonos";

$TTS["MELOTTS"]["endpoint"]='http://127.0.0.1:8084';	//Endpoint URL. Should be 'http://127.0.0.1:8084' if using default installation
$TTS["MELOTTS"]["language"]='EN';	//Language Model. Should be EN if using default installation
$TTS["MELOTTS"]["speed"]=1;	//Speech Speed
$TTS["MELOTTS"]["voiceid"]='malenord';	//Voice ID. Should be set automatically for most Vanilla Skyrim NPCs. Uses Skyrim VoiceType ID, e.g. femaleeventoned.<b> Click the help/doc link for full list of voiceids!</b>

$TTS["XTTSFASTAPI"]["endpoint"]='http://127.0.0.1:8020';	//Endpoint URL. Leave as default if you use CHIM XTTS.<br>You can run it on the cloud or use Mantella XTTS. Click the help link to learn more.
$TTS["XTTSFASTAPI"]["language"]='en';	//Language
$TTS["XTTSFASTAPI"]["voiceid"]='TheNarrator';	//Generated voice file name. Click the help link to go to XTTS management.
$TTS["XTTSFASTAPI"]["voicelogic"]='voicetype';	//Default profile only. Logic used for generating [TTS XTTSFASTAPI voiceid] <br> voicetype = NPC voicetype ID (femalenord) <br> name = NPC name (mjoll_the_lioness)

$TTS["MIMIC3"]["URL"]='http://127.0.0.1:59125';	//MIMIC3 Service URL.
$TTS["MIMIC3"]["voice"]='en_UK/apope_low#default';	//Voice ID code
$TTS["MIMIC3"]["rate"]=1;	//Voice speed
$TTS["MIMIC3"]["volume"]='60';	//Voice Volume

$TTS["XVASYNTH"]["url"]='http://192.168.0.1:8008';	//xVASynth should be running on the same machine as DwemerDistro!<br>Must use your computers IP Address.<br>Can be found by running the command 'ipconfig' in your CMD prompt.<br>Example: http://your-local-ip:8008<br>Click the <b>help/doc</b> link for more info.
$TTS["XVASYNTH"]["base_lang"]='en';	//Base language
$TTS["XVASYNTH"]["modelType"]='xVAPitch';	//Model Type
$TTS["XVASYNTH"]["version"]='3.0';	//xVASynth version (e.g. 3.0 is default. Older models are 1.0 or 2.0)
$TTS["XVASYNTH"]["game"]='skyrim';	//xVASynth gameID (e.g. skyrim)
$TTS["XVASYNTH"]["model"]='sk_malenord';	//xVASynth voiceID (e.g. sk_femaleeventoned)
$TTS["XVASYNTH"]["pace"]=1;	//Pace
$TTS["XVASYNTH"]["waveglowPath"]='resources/app/models/waveglow_256channels_universal_v4.pt';	//Wave Glow Path (relative)
$TTS["XVASYNTH"]["vocoder"]='n/a';	//Vocoder
$TTS["XVASYNTH"]["distroname"]='DwemerAI4Skyrim3';	//Leave as default!

$TTS["AZURE"]["fixedMood"]='';	//Force mood (voice style)
$TTS["AZURE"]["region"]='westeurope';	//Region location of your API key
$TTS["AZURE"]["voice"]='en-US-NancyNeural';	//Voice
$TTS["AZURE"]["volume"]='20';	//Volume
$TTS["AZURE"]["rate"]=1.25;	//Talk speed
$TTS["AZURE"]["countour"]='(11%, +15%) (60%, -23%) (80%, -34%)';	//Voice contour
$TTS["AZURE"]["validMoods"]=["whispering","default","dazed"];	//Allowed voice styles
$TTS["AZURE"]["API_KEY"]='';	//Azure TTS API KEY

$TTS["openai"]["endpoint"]='https://api.openai.com/v1/audio/speech';	//Endpoint URL
$TTS["openai"]["API_KEY"]='';	//API KEY
$TTS["openai"]["voice"]='nova';	//Voice ID
$TTS["openai"]["model_id"]='tts-1';	//Model
$TTS["openai"]["instructions"]='';	//Control the voice of your generated audio with additional instructions. Does not work with tts-1 or tts-1-hd.

$TTS["ELEVEN_LABS"]["voice_id"]='EXAVITQu4vr4xnSDxMaL';	//Voice code
$TTS["ELEVEN_LABS"]["optimize_streaming_latency"]='0';	//Optimize Streaming Latency
$TTS["ELEVEN_LABS"]["model_id"]='eleven_monolingual_v1';	//Model ID
$TTS["ELEVEN_LABS"]["stability"]=0.75;	//Stability
$TTS["ELEVEN_LABS"]["similarity_boost"]=0.75;	//Similarity_Boost
$TTS["ELEVEN_LABS"]["style"]=0;	//Style
$TTS["ELEVEN_LABS"]["API_KEY"]='';	//Eleven Labs API key.

$TTS["GCP"]["GCP_SA_FILEPATH"]='meta-chassis-391906-122bdf85aa6f.json';	//Google Cloud Platform auth file. Should be placed in the data folder.
$TTS["GCP"]["voice_name"]='en-GB-Neural2-C';	//Voice
$TTS["GCP"]["voice_languageCode"]='en-GB';	//Language code
$TTS["GCP"]["ssml_rate"]=1.15;	//Rate
$TTS["GCP"]["ssml_pitch"]='+3.6st';	//Pitch

$TTS["CONVAI"]["endpoint"]='https://api.convai.com/tts';	//Endpoint URL
$TTS["CONVAI"]["API_KEY"]='';	//API KEY
$TTS["CONVAI"]["language"]='en-US';	//Language
$TTS["CONVAI"]["voiceid"]='WUFemale3';	//Voice id (check compatability with language)

$TTS["KOKORO"]["endpoint"]='http://127.0.0.1:8880';	//Endpoint URL
$TTS["KOKORO"]["voiceid"]='af_bella';	//Voice id (check compatability with language)
$TTS["KOKORO"]["speed"]=1;	//Speed

$TTS["koboldcpp"]["endpoint"]='http://127.0.0.1:5001/api/extra/tts';	//Endpoint URL
$TTS["koboldcpp"]["voice"]='kobo';	//Voice to use

$TTS["ZONOS_GRADIO"]["endpoint"]='http://127.0.0.1:7860';	//Endpoint URL.
$TTS["ZONOS_GRADIO"]["language"]='en-us';	//Language
$TTS["ZONOS_GRADIO"]["model"]='Zyphra/Zonos-v0.1-transformer';	//Default profile only. Model to use.
$TTS["ZONOS_GRADIO"]["dynamic_tones"]=true;	//Default profile only. Enhance emotional quality by requesting values from the LLM. If false, emotions will be determined by the LLM-selected mood.
$TTS["ZONOS_GRADIO"]["voiceid"]='TheNarrator';	//Generated voice file name. Works the same as XTTSFASTAPI's voiceid and uses its voicelogic setting.
$TTS["ZONOS_GRADIO"]["pitch_std"]=45;	//Pitch standard deviation [0-300]
$TTS["ZONOS_GRADIO"]["speaking_rate"]=14.6;	//Speaking rate. Higher is faster. [5-30]
$TTS["ZONOS_GRADIO"]["cfg_scale"]=4.5;	//CFG scale. Controls how closely the audio matches the sample voice. Higher numbers will be a closer match. [1.1 - 5]

$TTS["ZONOS"]["endpoint"]="http://127.0.0.1:8765";	//Zonos streaming service endpoint
$TTS["ZONOS"]["model"]="transformer";	//Model type: 'transformer' or 'hybrid'
$TTS["ZONOS"]["device"]="cuda";	//Device: 'cuda' or 'cpu'
$TTS["ZONOS"]["language"]="en-us";	//Language code (en-us, es-es, etc.)
$TTS["ZONOS"]["speaker_voice"]="assets/exampleaudio.mp3";	//Path to reference voice audio file
$TTS["ZONOS"]["chunk_schedule"]="[16,9,12,15,20,30,50,80]";	//JSON array of chunk sizes for streaming
$TTS["ZONOS"]["chunk_overlap"]=2;	//Overlap between chunks for crossfading
$TTS["ZONOS"]["cfg_scale"]=2.0;	//Classifier-free guidance scale
$TTS["ZONOS"]["max_new_tokens"]=2580;	//Maximum tokens to generate (86*30 = ~30 seconds)
$TTS["ZONOS"]["streaming_enabled"]=true;	//Enable progressive audio streaming
$TTS["ZONOS"]["timeout"]=30;	//HTTP timeout for streaming requests

//Text-to-Speech service options. Used to generate your voice.
$TTSFUNCTION_PLAYER="none";

//VoiceID to use for the player character.
$TTSFUNCTION_PLAYER_VOICE="malenord";

//Speech-to-Text service options. Translates your voice to text.
$STTFUNCTION="whisper";

$STT["WHISPER"]["LANG"]='en';	//Language to detect for STT.
$STT["WHISPER"]["TRANSLATE"]=false;	//Will try to translate to english.
$STT["WHISPER"]["API_KEY"]='';	//OpenAI API key. Same used for OpenAI/ChatGPT AI service.

$STT["AZURE"]["LANG"]='en-US';	//Language to detect for STT.
$STT["AZURE"]["profanity"]='masked';	//Specifies how to handle profanity in recognition results. Accepted values are:<br>MASKED, which replaces profanity with asterisks.<br>REMOVED, which removes all profanity from the result.<br>RAW, which includes profanity in the result.
$STT["AZURE"]["API_KEY"]='';	//Azure API key. Same used for Azure TTS Service.

$STT["LOCALWHISPER"]["URL"]='http://127.0.0.1:9876/api/v0/transcribe';	//Local whisper endpoint. Leave as Default if you installed whisper through the Distro.
$STT["LOCALWHISPER"]["FORMFIELD"]='audio_file';	//Form field name for audio file. Sometimes needed to change to file to use another shiper implementations

$STT["DEEPGRAM"]["API_KEY"]='';	//Deepgram API key.
$STT["DEEPGRAM"]["LANG"]='';	//Language
$STT["DEEPGRAM"]["MODEL"]='nova-2';	//Model to use


//Image recognition aka Soulgaze spell. OpenAI also works as a connector to OpenRouter!
$ITTFUNCTION="openai";

$ITT["openai"]["url"]='https://api.openai.com/v1/chat/completions';	//OpenAI API or OpenRouter endpoint. Use this for OpenRouter (https://openrouter.ai/api/v1/chat/completions)
$ITT["openai"]["model"]='gpt-4o-mini';	//Model to use
$ITT["openai"]["max_tokens"]='1024';	//Maximum tokens to generate
$ITT["openai"]["detail"]='low';	//Low or high fidelity image understanding
$ITT["openai"]["API_KEY"]='';	//OpenAI API key
$ITT["openai"]["AI_VISION_PROMPT"]='Let\'s roleplay in the world of Skyrim. Describe this Skyrim image as if it is real life. Describe the environment, objects, and people you see at a fifth grade reading level. Ignore video game HUD and UI elements in your description.';	//Prompt to send to the OpenAI vision model.
$ITT["openai"]["AI_PROMPT"]='#HERIKA_NPC1# describes what they are seeing';	//Prompt for the AI NPC to follow when describing the scene.

$ITT["google_openai"]["url"]='https://generativelanguage.googleapis.com/v1beta/openai/chat/completions';	//Google OpenAI API.
$ITT["google_openai"]["model"]='gemini-1.5-flash';	//Model to use
$ITT["google_openai"]["max_tokens"]='1024';	//Maximum tokens to generate
$ITT["google_openai"]["detail"]='low';	//Low or high fidelity image understanding
$ITT["google_openai"]["API_KEY"]='';	//OpenAI API key
$ITT["google_openai"]["AI_VISION_PROMPT"]='Let\'s roleplay in the world of Skyrim. Describe this Skyrim image as if it is real life. Describe the environment, objects, and people you see at a fifth grade reading level. Ignore video game HUD and UI elements in your description.';	//Prompt to send to the OpenAI vision model.
$ITT["google_openai"]["AI_PROMPT"]='#HERIKA_NPC1# describes what they are seeing';	//Prompt for the AI NPC to follow when describing the scene.

$ITT["llamacpp"]["URL"]='http://127.0.0.1:8007';	//URL of the llama.cpp server
$ITT["llamacpp"]["AI_VISION_PROMPT"]='USER:Context, roleplay In Skyrim universe, #HERIKA_NPC1# watchs this scene:[img-1]. Describe the vision while keeping roleplay. Describe COLORS and SHAPES';	//Prompt to send to the llama vision model.
$ITT["llamacpp"]["AI_PROMPT"]='';	//Prompt for the AI NPC to follow when describing the scene.

$FEATURES["MEMORY_EMBEDDING"]["ENABLED"]=true;	//<Strong>Make sure CONNECTORS_DIARY is setup!</strong> Enable long term memory. It will provide the most relevant memory with every AI response to be used as context.
$FEATURES["MEMORY_EMBEDDING"]["TXTAI_URL"]='http://127.0.0.1:8083';	//NOT FUNCTIONAL CURRENTLY. JUST LEAVE AS IS!
$FEATURES["MEMORY_EMBEDDING"]["MEMORY_TIME_DELAY"]='10';	//Time in minutes to delay before using a memory in a prompt. Used to avoid pushing recent dialogues as memories.
$FEATURES["MEMORY_EMBEDDING"]["MEMORY_CONTEXT_SIZE"]='1';	//The amount of the most relevant memory records that will be injected into the prompt.
$FEATURES["MEMORY_EMBEDDING"]["AUTO_CREATE_SUMMARYS"]=false;	//Will combine individual memory logs into larger ones. Is more accurate for memory recollection but will use up more tokens. If using koboldcpp, use the multiuser mode to avoid locking.
$FEATURES["MEMORY_EMBEDDING"]["AUTO_CREATE_SUMMARY_INTERVAL"]='10';	//Time frame used to pack summary data. 10 = 13 in-game hours | 5 = 7.5 in-game hours etc
$FEATURES["MEMORY_EMBEDDING"]["MEMORY_BIAS_A"]=33;	//From 0 (never) to 100 (always). Minimal distance to offer memory.
$FEATURES["MEMORY_EMBEDDING"]["MEMORY_BIAS_B"]=66;	//From 0 (never) to 100 (always). Minimal distance to offer and endorse memory.

$FEATURES["MISC"]["ADD_TIME_MARKS"]=false;	//Add timestamps to the context logs. Helps with memory recollection.
$FEATURES["MISC"]["ITT_QUALITY"]='90';	//Only for Soulgaze HD. Compression quality can be set from 0 (lower and unusable) to 100 (near no compression). More quality means higher file size, ergo more tokens.
$FEATURES["MISC"]["TTS_RANDOM_PITCH"]=false;	//WIP DO NOT USE! Adjusting the pitch when generating the voice for this actor will add variation, so actors using the same voice sound slightly distinct.
$FEATURES["MISC"]["LIFE_LINK_PLUGIN"]=false;	//WIP. Is disabled currently, do not enable is a work in progress.
?>