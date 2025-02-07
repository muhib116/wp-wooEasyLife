import { getTutorials } from "@/remoteApi";
import axios from "axios";
import { ref, computed, onMounted } from "vue";

export const toggleVideoPlayer = ref(false);
export const activeTutorialList = ref([]);

// export const tutorialsList = {
//     dashboard: [
//       {
//         title: "",
//         path: "https://www.youtube.com/watch?v=uFcrJJiDksY",
//       },
//       {
//         title: "",
//         path: "https://www.youtube.com/watch?v=2vd2eJb1JG0",
//       },
//       {
//         title: "",
//         path: "https://www.youtube.com/watch?v=8I9jbS4_GxE",
//       }
//     ],
//     orders: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=8I9jbS4_GxE",
//         },
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=hxMNYkLN7tI",
//         }
//     ],
//     missingOrders: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=Hn4a1OD4sZc",
//         }
//     ],
//     blackList: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=llw1eCZ-gNc",
//         }
//     ],
//     fraudCheck: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=hxMNYkLN7tI",
//         }
//     ],
//     license: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=mON7oIUUgKw",
//         }
//     ],
//     smsConfig: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=CKJA9blyMUg",
//         }
//     ],
//     sendSms: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=KJbF8x83UnM",
//         }
//     ],
//     integration: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=l6RYjEZAVug",
//         }
//     ],
//     courier: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=vxO1KgZuZ40",
//         }
//     ],
//     customStatus: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=MhXvQWvxENA",
//         }
//     ],
//     smsRecharge: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=yxXzMqyHFJc",
//         }
//     ],
//     marketingTools: [
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=ijBxe70sd8M",
//         },
//         {
//           title: "",
//           path: "https://www.youtube.com/watch?v=P1fIdFRnfqw",
//         }
//     ],
// }

export const tutorialsList = ref({})
export const setActiveTutorialList = (category) => {
    if (tutorialsList.value[category] && tutorialsList.value[category].length) {
      activeTutorialList.value = tutorialsList.value[category];
      toggleVideoPlayer.value = true;
    } else {
      activeTutorialList.value = [];
      toggleVideoPlayer.value = false;
    }
}

export const useTutorials = () => {

  const extractVideoId = (url) => {
    const regex = /(?:youtube\.com\/.*v=|youtu\.be\/)([^?&]+)/;
    const match = url.match(regex);
    return match ? match[1] : null;
  };

  const currentIndex = ref(0);
  // Reactive states
  const hasActiveTutorials = computed(
    () => activeTutorialList.value.length > 0
  );

  const resetTutorials = () => {
    activeTutorialList.value = [];
    toggleVideoPlayer.value = false;
  }

  const currentVideoId = computed(() =>
    extractVideoId(activeTutorialList.value[currentIndex.value].path)
  );

  // Fetch video title using YouTube oEmbed API
  const fetchVideoTitle = async (videoObj: {
    path: string
    title: string
  }) => {
    try {
      const videoId = extractVideoId(videoObj.path)
      if (!videoId) return "Unknown Video"
      const { data } = await axios.get(
        `https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=${videoId}&format=json`
      )
      videoObj.title = data.title
    } catch (error) {
      console.error("Error fetching video title:", error)
      return "Unknown Video"
    }
  }

  onMounted(async () => {
    if(tutorialsList.value?.length) return
    const { data } = await getTutorials()
    tutorialsList.value = data
  })

  return {
    currentIndex,
    tutorialsList,
    currentVideoId,
    toggleVideoPlayer,
    hasActiveTutorials,
    activeTutorialList,
    extractVideoId,
    resetTutorials,
    setActiveTutorialList,
    fetchVideoTitle
  };
};
